
class LaTeXEditor {
    constructor() {
        this.editor = null;
        this.autoCompileTimeout = null;
        this.isCompiling = false;
        this.currentPdfUrl = null;
        this.helpPopupShown = false;
        this.isAnalyzing = false;
        this.correctedCode = null;

        this.suppressExtensionErrors();
        
        this.init();
    }
    
    suppressExtensionErrors() {
        window.addEventListener('error', (event) => {
            if (event.message && (
                event.message.includes('message channel closed') ||
                event.message.includes('Extension context invalidated') ||
                event.filename === 'chrome-extension://' ||
                event.filename === 'moz-extension://'
            )) {
                event.preventDefault();
                event.stopPropagation();
                return false;
            }
        });
        
        window.addEventListener('unhandledrejection', (event) => {
            if (event.reason && event.reason.message && 
                event.reason.message.includes('message channel closed')) {
                event.preventDefault();
                return false;
            }
        });
    }

    init() {
        this.initEditor();
        this.bindEvents();
        this.initTemplates();
        this.initResizer();
        this.updateStats();
    }

    initEditor() {
        const textarea = document.getElementById('latexEditor');
        this.editor = CodeMirror.fromTextArea(textarea, {
            mode: 'stex',
            theme: 'monokai',
            lineNumbers: true,
            lineWrapping: true,
            autoCloseBrackets: true,
            matchBrackets: true,
            indentUnit: 2,
            tabSize: 2,
            extraKeys: {
                'Ctrl-Enter': () => this.compilePDF(),
                'F11': () => this.toggleFullscreen(),
                'Esc': () => this.exitFullscreen()
            },
            viewportMargin: Infinity
        });

        this.editor.on('change', () => {
            this.updateStats();
            this.handleAutoCompile();
        });
        setTimeout(() => this.editor.focus(), 100);
    }

    extractCompleteCode(aiResponse) {
        // Try to extract LaTeX code from AI response
        // Look for patterns like ```latex...``` or CORRECTED CODE: sections
        
        // Pattern 1: ```latex...```
        const codeBlockMatch = aiResponse.match(/```latex\s*\n([\s\S]*?)\n```/);
        if (codeBlockMatch) {
            return codeBlockMatch[1].trim();
        }
        
        // Pattern 2: CORRECTED CODE: followed by code
        const correctedMatch = aiResponse.match(/CORRECTED CODE:\s*\n```latex\s*\n([\s\S]*?)\n```/);
        if (correctedMatch) {
            return correctedMatch[1].trim();
        }
        
        // Pattern 3: Look for LaTeX document structure
        const docMatch = aiResponse.match(/\\documentclass[\s\S]*?\\end\{document\}/);
        if (docMatch) {
            return docMatch[0].trim();
        }
        
        // Pattern 4: Simple code block without language specification
        const simpleBlockMatch = aiResponse.match(/```\s*\n(\\documentclass[\s\S]*?\\end\{document\})\s*\n```/);
        if (simpleBlockMatch) {
            return simpleBlockMatch[1].trim();
        }
        
        return null;
    }
    
    bindEvents() {
        // Safe element binding - check if element exists before adding event listener
        const backToChat = document.getElementById('backToChat');
        if (backToChat) {
            backToChat.addEventListener('click', () => {
                window.location.href = '../index.php';
            });
        }

        const compileBtn = document.getElementById('compileBtn');
        if (compileBtn) {
            compileBtn.addEventListener('click', () => {
                this.compilePDF();
            });
        }

        // Ctrl+Space shortcut for applying corrected code
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.code === 'Space') {
                e.preventDefault();
                this.applyFixedCode();
            }
        });

        const autoCompile = document.getElementById('autoCompile');
        if (autoCompile) {
            autoCompile.addEventListener('change', (e) => {
                if (e.target.checked) {
                    this.showToast('Auto-compile enabled', 'success');
                    this.handleAutoCompile();
                } else {
                    this.showToast('Auto-compile disabled', 'success');
                    clearTimeout(this.autoCompileTimeout);
                }
            });
        }

        const templateSelect = document.getElementById('templateSelect');
        if (templateSelect) {
            templateSelect.addEventListener('change', (e) => {
                if (e.target.value) {
                    this.loadTemplate(e.target.value);
                    e.target.value = '';
                }
            });
        }

        const paperSelect = document.getElementById('paperSelect');
        if (paperSelect) {
            paperSelect.addEventListener('change', (e) => {
                this.updatePaperSize(e.target.value);
            });
        }

        // AI Fix button - NO RECURSION
        const fixWithAIBtn = document.getElementById('fixWithAI');
        if (fixWithAIBtn) {
            fixWithAIBtn.addEventListener('click', async () => {
                await this.handleAIFix();
            });
        }

        const citationBtn = document.getElementById('citationBtn');
        if (citationBtn) {
            citationBtn.addEventListener('click', () => {
                this.generateCitation();
            });
        }

        const clearBtn = document.getElementById('clearBtn');
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                if (confirm('Are you sure you want to clear all content?')) {
                    this.editor.setValue('');
                    this.showToast('Editor cleared', 'success');
                }
            });
        }

        const downloadBtn = document.getElementById('downloadBtn');
        if (downloadBtn) {
            downloadBtn.addEventListener('click', () => {
                this.downloadPDF();
            });
        }

        const fullscreenBtn = document.getElementById('fullscreenBtn');
        if (fullscreenBtn) {
            fullscreenBtn.addEventListener('click', () => {
                this.toggleFullscreen();
            });
        }

        document.querySelectorAll('.toast-close').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.target.closest('.toast').style.display = 'none';
            });
        });

        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                this.compilePDF();
            }
            
            // Ctrl+Space for AI auto-fix
            if (e.ctrlKey && e.code === 'Space') {
                e.preventDefault();
                this.handleAIFix();
            }
        });
        
        // Initialize help system
        this.initHelpSystem();
    }

    // NEW METHOD - NO RECURSION
    async handleAIFix() {
        try {
            // Prevent multiple simultaneous calls
            if (this.isAnalyzing) {
                this.showToast('Analysis already in progress...', 'info');
                return;
            }

            // If we already have corrected code, apply it
            if (this.correctedCode) {
                this.applyAICorrection();
                return;
            }

            // Otherwise, run analysis first
            await this.performAIAnalysis();
            
        } catch (error) {
            console.error('AI Fix Error:', error);
            this.showToast('AI fix failed: ' + error.message, 'error');
        }
    }

    // Apply AI correction without recursion
    applyAICorrection() {
        if (!this.correctedCode) {
            this.showToast('No corrected code available', 'error');
            return;
        }

        const currentCode = this.editor.getValue();
        const correctedLength = this.correctedCode.length;
        const currentLength = currentCode.length;

        // Safety check
        if (currentLength > 1000 && correctedLength < currentLength * 0.5) {
            const confirmed = confirm(
                `⚠️ SAFETY CHECK ⚠️\n\n` +
                `Current code: ${currentLength} characters\n` +
                `AI suggestion: ${correctedLength} characters\n\n` +
                `The AI suggestion is much shorter. Continue?`
            );

            if (!confirmed) {
                this.showToast('Fix cancelled', 'info');
                return;
            }
        }

        // Apply the fix
        this.editor.setValue(this.correctedCode);
        this.showToast('AI fix applied! Compiling...', 'success');

        setTimeout(() => {
            this.compilePDF();
        }, 500);
    }

    // Perform AI analysis without recursion
    async performAIAnalysis() {
        if (this.isAnalyzing) {
            return;
        }

        this.isAnalyzing = true;

        try {
            const latexCode = this.editor.getValue();
            if (!latexCode.trim()) {
                this.showToast('No LaTeX code to analyze', 'error');
                return;
            }

            this.showToast('Analyzing with AI...', 'info');
            const errorMessage = this.getLastErrorMessage();

            const response = await fetch('ai-api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'analyze_errors',
                    latex_code: latexCode,
                    error_message: errorMessage || 'General analysis requested'
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();

            if (result.success && result.data) {
                if (result.data.corrected_code) {
                    this.correctedCode = result.data.corrected_code;
                    this.showToast('AI analysis complete! Click "Fix with AI" again to apply.', 'success');
                    this.displayAnalysisResult(result.data);
                } else {
                    this.showToast('AI analysis complete, but no corrections suggested.', 'info');
                    this.displayAnalysisResult(result.data);
                }
            } else {
                throw new Error(result.message || 'AI analysis failed');
            }

        } catch (error) {
            console.error('AI Analysis Error:', error);
            this.showToast('AI analysis failed: ' + error.message, 'error');
        } finally {
            this.isAnalyzing = false;
        }
    }

    displayAnalysisResult(analysisData) {
        const resultContainer = document.getElementById('aiAnalysisResult');
        if (!resultContainer) return;

        let resultHTML = '<h4>AI Analysis Result:</h4>';

        if (analysisData.analysis) {
            resultHTML += `<div class="analysis-text">${analysisData.analysis}</div>`;
        }

        if (analysisData.suggestions && analysisData.suggestions.length > 0) {
            resultHTML += '<h5>Suggestions:</h5><ul>';
            analysisData.suggestions.forEach(suggestion => {
                resultHTML += `<li>${suggestion}</li>`;
            });
            resultHTML += '</ul>';
        }

        if (analysisData.corrected_code) {
            resultHTML += '<div class="mt-2"><strong>Corrected code is ready to apply!</strong></div>';
        }

        resultContainer.innerHTML = resultHTML;
        resultContainer.style.display = 'block';
    }

    getLastErrorMessage() {
        const errorContainer = document.getElementById('errorDisplay');
        if (errorContainer && errorContainer.style.display !== 'none') {
            return errorContainer.textContent || 'No specific error message available';
        }
        return 'Please analyze your LaTeX code for errors';
    }

    initTemplates() {
        this.templates = {
            article: `\\documentclass[a4paper]{article}
\\usepackage[utf8]{inputenc}
\\usepackage{amsmath}
\\usepackage{amsfonts}
\\usepackage{amssymb}
\\usepackage{graphicx}

\\title{Article Title}
\\author{Your Name}
\\date{\\today}

\\begin{document}

\\maketitle

\\begin{abstract}
Your abstract goes here.
\\end{abstract}

\\section{Introduction}
Your introduction goes here.

\\section{Methodology}
Describe your methodology.

\\section{Results}
Present your results.

\\section{Conclusion}
Your conclusion goes here.

\\end{document}`,

            report: `\\documentclass[a4paper]{report}
\\usepackage[utf8]{inputenc}
\\usepackage{amsmath}
\\usepackage{amsfonts}
\\usepackage{amssymb}
\\usepackage{graphicx}
\\usepackage{hyperref}

\\title{Report Title}
\\author{Your Name}
\\date{\\today}

\\begin{document}

\\maketitle
\\tableofcontents

\\chapter{Introduction}
Your introduction goes here.

\\chapter{Literature Review}
Review of existing literature.

\\chapter{Methodology}
Describe your methodology.

\\chapter{Results and Discussion}
Present and discuss your results.

\\chapter{Conclusion}
Your conclusion goes here.

\\bibliographystyle{plain}
\\bibliography{references}

\\end{document}`,

            book: `\\documentclass[a4paper]{book}
\\usepackage[utf8]{inputenc}
\\usepackage{amsmath}
\\usepackage{amsfonts}
\\usepackage{amssymb}
\\usepackage{graphicx}
\\usepackage{hyperref}

\\title{Book Title}
\\author{Your Name}
\\date{\\today}

\\begin{document}

\\frontmatter
\\maketitle
\\tableofcontents

\\mainmatter

\\part{Part One}

\\chapter{Introduction}
Your introduction goes here.

\\chapter{Chapter Two}
Content for chapter two.

\\part{Part Two}

\\chapter{Advanced Topics}
Advanced content goes here.

\\backmatter

\\bibliographystyle{plain}
\\bibliography{references}

\\end{document}`,

            letter: `\\documentclass{letter}
\\usepackage[utf8]{inputenc}

\\signature{Your Name}
\\address{Your Address \\\\ City, State ZIP \\\\ Email: your.email@example.com}

\\begin{document}

\\begin{letter}{Recipient Name \\\\ Recipient Address \\\\ City, State ZIP}

\\opening{Dear Sir/Madam,}

Your letter content goes here. This is a professional letter template that you can customize according to your needs.

You can add multiple paragraphs and format your content appropriately.

\\closing{Sincerely,}

\\end{letter}

\\end{document}`,

            beamer: `\\documentclass[aspectratio=169]{beamer}
\\usepackage[utf8]{inputenc}
\\usepackage{amsmath}
\\usepackage{amsfonts}
\\usepackage{amssymb}
\\usepackage{graphicx}

\\usetheme{Madrid}
\\usecolortheme{default}

\\title[Short Title]{Your Presentation Title}
\\author{Your Name}
\\institute{Your Institution}
\\date{\\today}

\\begin{document}

\\frame{\\titlepage}

\\begin{frame}
\\frametitle{Outline}
\\tableofcontents
\\end{frame}

\\section{Introduction}

\\begin{frame}
\\frametitle{Introduction}
\\begin{itemize}
    \\item First point
    \\item Second point
    \\item Third point
\\end{itemize}
\\end{frame}

\\section{Main Content}

\\begin{frame}
\\frametitle{Main Content}
Your main content goes here.

\\begin{equation}
E = mc^2
\\end{equation}
\\end{frame}

\\section{Conclusion}

\\begin{frame}
\\frametitle{Conclusion}
\\begin{itemize}
    \\item Summary point 1
    \\item Summary point 2
    \\item Thank you!
\\end{itemize}
\\end{frame}

\\end{document}`,

            math: `\\documentclass[a4paper]{article}
\\usepackage[utf8]{inputenc}
\\usepackage{amsmath}
\\usepackage{amsfonts}
\\usepackage{amssymb}
\\usepackage{amsthm}
\\usepackage{mathtools}
\\usepackage{tikz}

\\newtheorem{theorem}{Theorem}
\\newtheorem{lemma}{Lemma}
\\newtheorem{definition}{Definition}

\\title{Mathematical Document}
\\author{Your Name}
\\date{\\today}

\\begin{document}

\\maketitle

\\section{Basic Equations}

The famous Einstein equation:
\\begin{equation}
E = mc^2
\\end{equation}

\\section{Advanced Mathematics}

\\begin{theorem}
Let $f: \\mathbb{R} \\to \\mathbb{R}$ be a continuous function. Then...
\\end{theorem}

\\begin{proof}
The proof goes here...
\\end{proof}

\\section{Matrix Operations}

\\begin{equation}
\\begin{pmatrix}
a & b \\\\
c & d
\\end{pmatrix}
\\begin{pmatrix}
x \\\\
y
\\end{pmatrix}
=
\\begin{pmatrix}
ax + by \\\\
cx + dy
\\end{pmatrix}
\\end{equation}

\\section{Integral Calculus}

\\begin{align}
\\int_{-\\infty}^{\\infty} e^{-x^2} dx &= \\sqrt{\\pi} \\\\
\\frac{d}{dx} \\int_{a}^{x} f(t) dt &= f(x)
\\end{align}

\\end{document}`
        };
    }

    loadTemplate(templateName) {
        if (this.templates[templateName]) {
            if (this.editor.getValue().trim() && 
                !confirm('This will replace current content. Continue?')) {
                return;
            }
            
            this.editor.setValue(this.templates[templateName]);
            this.showToast(`${templateName.charAt(0).toUpperCase() + templateName.slice(1)} template loaded`, 'success');

            if (document.getElementById('autoCompile').checked) {
                this.handleAutoCompile();
            }
        }
    }

    updatePaperSize(paperSize) {
        const content = this.editor.getValue();
        const currentPaperSize = document.getElementById('paperSelect').value;
        
        const updatedContent = content.replace(
            /\\documentclass\[[^\]]*\]/,
            (match) => {
                return match.replace(/[a-z0-9]+paper/i, currentPaperSize);
            }
        );

        if (!content.includes('\\documentclass')) {
            const newContent = `\\documentclass[${currentPaperSize}]{article}\n${content}`;
            this.editor.setValue(newContent);
        } else {
            this.editor.setValue(updatedContent);
        }
        
        this.showToast(`Paper size changed to ${paperSize}`, 'success');

        if (document.getElementById('autoCompile').checked) {
            this.handleAutoCompile();
        }
    }

    async generateCitation() {
        const url = prompt('Enter URL or article details for citation:');
        if (!url || url.trim() === '') return;

        const citationBtn = document.getElementById('citationBtn');
        if (!citationBtn) {
            console.error('Citation button not found');
            return;
        }

        const originalText = citationBtn.innerHTML;
        citationBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
        citationBtn.disabled = true;

        try {
            const response = await fetch('ai-api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'generate_citation',
                    input: url.trim(),
                    style: 'ieee'
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();

            if (result.success && result.data && result.data.citation) {
                const citation = result.data.citation;

                const cursor = this.editor.getCursor();
                this.editor.replaceRange('\n' + citation + '\n', cursor);
                this.showToast('Citation generated and inserted!', 'success');
            } else {
                console.error('Citation generation failed:', result);
                this.showToast(result.message || 'Citation generation failed', 'error');
            }
        } catch (error) {
            console.error('Citation generation error:', error);

            if (error.name === 'AbortError') {
                this.showToast('Citation generation timed out', 'error');
            } else if (error.name === 'TypeError' && error.message.includes('fetch')) {
                this.showToast('Network error - Please check your connection', 'error');
            } else if (error.message.includes('HTTP error')) {
                this.showToast('Server error - Please try again later', 'error');
            } else {
                this.showToast('Citation generation failed', 'error');
            }
        } finally {
            if (citationBtn) {
                citationBtn.innerHTML = originalText;
                citationBtn.disabled = false;
            }
        }
    }

    async compilePDF() {
        if (this.isCompiling) {
            this.showToast('Compilation already in progress...', 'info');
            return;
        }
        
        const latexCode = this.editor.getValue().trim();
        if (!latexCode) {
            this.showToast('Please enter LaTeX code first', 'error');
            return;
        }

        this.isCompiling = true;
        this.setCompileStatus('compiling');
        
        try {
            const response = await fetch('compile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    latex: latexCode
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.currentPdfUrl = result.pdf_url;
                this.displayPDF(result.pdf_url);
                this.setCompileStatus('success');
                
                // Clear any previous errors
                const errorContainer = document.getElementById('errorDisplay');
                if (errorContainer) {
                    errorContainer.style.display = 'none';
                }
                
                // Show warnings if they exist
                if (result.warnings && result.warnings.includes('WARNING')) {
                    this.showToast('PDF compiled with warnings - Check output panel', 'warning');
                    this.displayWarnings(result.warnings);
                } else {
                    this.showToast('PDF compiled successfully!', 'success');
                }
            } else {
                this.displayError(result.error);
                this.setCompileStatus('error');
                this.showToast('Compilation failed - Multiple errors found', 'error');
            }
            
        } catch (error) {
            console.error('Compilation error:', error);
            this.displayError(`Network Error: ${error.message}`);
            this.setCompileStatus('error');
            this.showToast('Compilation failed - Check your connection', 'error');
        }
    }

    displayError(errorMessage) {
        const errorDisplay = document.getElementById('errorDisplay');
        if (!errorDisplay) return;

        // Clear corrected code when new errors appear
        this.correctedCode = null;

        errorDisplay.innerHTML = `
<div class="error-header">
    <h4><i class="fas fa-exclamation-triangle"></i> Compilation Error</h4>
    <div class="error-actions">
        <button id="analyzeErrorBtn" class="btn btn-sm btn-primary">
            <i class="fas fa-robot"></i> Fix with AI
        </button>
        <button id="showAllErrorsBtn" class="btn btn-sm btn-secondary">
            <i class="fas fa-list"></i> Show Details
        </button>
    </div>
</div>
<div class="error-content">
    <pre>${errorMessage}</pre>
</div>
<div id="aiAnalysisResult" style="display: none; margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 5px;"></div>`;
        
        errorDisplay.style.display = 'block';

        setTimeout(() => {
            const analyzeBtn = document.getElementById('analyzeErrorBtn');
            const showAllBtn = document.getElementById('showAllErrorsBtn');
            
            if (analyzeBtn) {
                analyzeBtn.addEventListener('click', () => {
                    this.handleAIFix();
                });
            }

            if (showAllBtn) {
                showAllBtn.addEventListener('click', () => this.showDetailedErrors(errorMessage));
            }
        }, 100);

        const downloadBtn = document.getElementById('downloadBtn');
        if (downloadBtn) downloadBtn.disabled = true;
        
        // Show help popup 4 seconds after error appears (only once per session)
        if (!this.helpPopupShown) {
            setTimeout(() => {
                this.showHelpPopup();
                this.helpPopupShown = true;
            }, 4000);
        }
    }

    displayWarnings(warnings) {
        const warningDisplay = document.getElementById('warningContainer');
        if (!warningDisplay) return;

        warningDisplay.innerHTML = `
<div class="warning-header">
    <h4><i class="fas fa-exclamation-circle"></i> Compilation Warnings</h4>
</div>
<div class="warning-content">
    <pre>${warnings}</pre>
</div>`;
        
        warningDisplay.style.display = 'block';
    }

    showDetailedErrors(errorMessage) {
        const modal = document.createElement('div');
        modal.className = 'error-modal';
        modal.innerHTML = `
<div class="error-modal-content">
    <div class="error-modal-header">
        <h3>Detailed Error Information</h3>
        <button class="error-modal-close">&times;</button>
    </div>
    <div class="error-modal-body">
        <pre>${errorMessage}</pre>
    </div>
</div>`;
        
        document.body.appendChild(modal);
        
        modal.querySelector('.error-modal-close').addEventListener('click', () => {
            document.body.removeChild(modal);
        });
        
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                document.body.removeChild(modal);
            }
        });
    }

    displayPDF(pdfUrl) {
        const pdfViewer = document.getElementById('pdfPreview');
        if (pdfViewer) {
            pdfViewer.src = pdfUrl + '?t=' + Date.now();
            pdfViewer.style.display = 'block';
        }

        const downloadBtn = document.getElementById('downloadBtn');
        if (downloadBtn) {
            downloadBtn.disabled = false;
        }
        
        // Hide welcome message when PDF is displayed
        const welcomeMessage = document.getElementById('welcomeMessage');
        if (welcomeMessage) {
            welcomeMessage.style.display = 'none';
        }
    }

    handleAutoCompile() {
        clearTimeout(this.autoCompileTimeout);
        
        const autoCompileCheckbox = document.getElementById('autoCompile');
        if (autoCompileCheckbox && autoCompileCheckbox.checked) {
            this.autoCompileTimeout = setTimeout(() => {
                this.compilePDF();
            }, 2000);
        }
    }

    updateStats() {
        const code = this.editor.getValue();
        const stats = this.getCodeStats(code);
        
        const statsElement = document.getElementById('editorStats');
        if (statsElement) {
            statsElement.innerHTML = `
                <span><i class="fas fa-file-lines"></i> ${stats.lines} lines</span>
                <span><i class="fas fa-font"></i> ${stats.chars} chars</span>
                <span><i class="fas fa-spell-check"></i> ${stats.words} words</span>
            `;
        }
    }

    getCodeStats(code) {
        return {
            lines: code.split('\n').length,
            chars: code.length,
            words: code.trim().split(/\s+/).length
        };
    }

    initHelpSystem() {
        // Help popup event listeners only - no automatic popup
        const helpYesBtn = document.getElementById('helpYesBtn');
        const helpNoBtn = document.getElementById('helpNoBtn');
        const closeShortcutsBtn = document.getElementById('closeShortcutsBtn');
        
        if (helpYesBtn) {
            helpYesBtn.addEventListener('click', () => {
                this.hideHelpPopup();
                this.showKeyboardShortcuts();
            });
        }
        
        if (helpNoBtn) {
            helpNoBtn.addEventListener('click', () => {
                this.hideHelpPopup();
            });
        }
        
        if (closeShortcutsBtn) {
            closeShortcutsBtn.addEventListener('click', () => {
                this.hideKeyboardShortcuts();
            });
        }
    }

    showHelpPopup() {
        const helpPopup = document.getElementById('helpPopup');
        if (helpPopup) {
            helpPopup.style.display = 'block';
        }
    }

    hideHelpPopup() {
        const helpPopup = document.getElementById('helpPopup');
        if (helpPopup) {
            helpPopup.style.display = 'none';
        }
    }
    
    showKeyboardShortcuts() {
        const keyboardShortcutsHelp = document.getElementById('keyboardShortcutsHelp');
        if (keyboardShortcutsHelp) {
            keyboardShortcutsHelp.style.display = 'block';
        }
    }
    
    hideKeyboardShortcuts() {
        const keyboardShortcutsHelp = document.getElementById('keyboardShortcutsHelp');
        if (keyboardShortcutsHelp) {
            keyboardShortcutsHelp.style.display = 'none';
        }
    }

    setCompileStatus(status) {
        const statusElement = document.getElementById('compileStatus');
        const spinner = document.getElementById('loadingSpinner');
        const compileBtn = document.getElementById('compileBtn');
        
        if (statusElement) {
            statusElement.className = `compile-status ${status}`;
            
            switch(status) {
                case 'compiling':
                    statusElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Compiling...';
                    if (compileBtn) compileBtn.disabled = true;
                    break;
                case 'success':
                    statusElement.innerHTML = '<i class="fas fa-check-circle"></i> Success';
                    if (compileBtn) compileBtn.disabled = false;
                    this.isCompiling = false;
                    break;
                case 'error':
                    statusElement.innerHTML = '<i class="fas fa-times-circle"></i> Error';
                    if (compileBtn) compileBtn.disabled = false;
                    this.isCompiling = false;
                    break;
                default:
                    statusElement.innerHTML = '<i class="fas fa-circle"></i> Ready';
                    if (compileBtn) compileBtn.disabled = false;
                    this.isCompiling = false;
            }
        }
    }

    downloadPDF() {
        if (this.currentPdfUrl) {
            const link = document.createElement('a');
            link.href = this.currentPdfUrl;
            link.download = 'document.pdf';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            this.showToast('PDF downloaded successfully!', 'success');
        }
    }

    toggleFullscreen() {
        const container = document.querySelector('.container');
        if (container) {
            container.classList.toggle('fullscreen');
        }
    }

    exitFullscreen() {
        const container = document.querySelector('.container');
        if (container) {
            container.classList.remove('fullscreen');
        }
    }

    applyFixedCode() {
        if (!this.correctedCode) {
            this.showToast('No corrected code available. Run AI Analysis first!', 'warning');
            return;
        }
        
        // Replace entire editor content with corrected code
        this.editor.setValue(this.correctedCode);
        this.showToast('Complete corrected code applied! ✅', 'success');
        this.updateStats();
        
        // Auto-compile if enabled
        const autoCompile = document.getElementById('autoCompile');
        if (autoCompile && autoCompile.checked) {
            setTimeout(() => this.compilePDF(), 1000);
        }
    }
    
    showToast(message, type = 'success') {
        const toast = document.getElementById(type === 'success' ? 'successToast' : 'errorToast');
        const messageElement = toast.querySelector(type === 'success' ? '#successMessage' : '#toastMessage');
        
        messageElement.textContent = message;
        toast.style.display = 'block';
        toast.classList.add('fade-in');
        
        setTimeout(() => {
            toast.style.display = 'none';
            toast.classList.remove('fade-in');
        }, 4000);
    }

    initResizer() {
        const resizer = document.querySelector('.resizer');
        const leftPanel = document.querySelector('.editor-panel');
        const rightPanel = document.querySelector('.preview-panel');
        let isResizing = false;

        resizer.addEventListener('mousedown', (e) => {
            isResizing = true;
            document.addEventListener('mousemove', handleMouseMove);
            document.addEventListener('mouseup', handleMouseUp);
        });

        function handleMouseMove(e) {
            if (!isResizing) return;
            
            const containerRect = document.querySelector('.main-content').getBoundingClientRect();
            const newLeftWidth = ((e.clientX - containerRect.left) / containerRect.width) * 100;
            
            if (newLeftWidth > 20 && newLeftWidth < 80) {
                leftPanel.style.width = newLeftWidth + '%';
                rightPanel.style.width = (100 - newLeftWidth) + '%';
            }
        }

        function handleMouseUp() {
            isResizing = false;
            document.removeEventListener('mousemove', handleMouseMove);
            document.removeEventListener('mouseup', handleMouseUp);
        }
    }
}

// Initialize the editor when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.latexEditor = new LaTeXEditor();
});