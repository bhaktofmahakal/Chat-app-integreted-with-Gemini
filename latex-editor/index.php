<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LaTeX Editor - Professional PDF Generator</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/stex/stex.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/monokai.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <h1><i class="fas fa-file-pdf"></i> LaTeX Editor</h1>
                <span class="subtitle">Professional PDF Generator with AI Support</span>
            </div>
            <div class="header-right">
                <button id="backToChat" class="btn btn-secondary">
                    <i class="fas fa-comments"></i> Back to Chat
                </button>
                <div class="compile-controls">
                    <label class="switch">
                        <input type="checkbox" id="autoCompile" checked>
                        <span class="slider round"></span>
                    </label>
                    <span class="switch-label">Auto-compile</span>
                    <button id="compileBtn" class="btn btn-primary">
                        <i class="fas fa-play"></i> Compile PDF
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Left Panel - LaTeX Editor -->
            <div class="editor-panel">
                <div class="panel-header">
                    <h3><i class="fas fa-code"></i> LaTeX Source</h3>
                    <div class="editor-tools">
                        <select id="templateSelect" class="template-select">
                            <option value="">Select Template</option>
                            <option value="article">Article</option>
                            <option value="report">Report</option>
                            <option value="book">Book</option>
                            <option value="letter">Letter</option>
                            <option value="beamer">Presentation</option>
                            <option value="math">Math Document</option>
                        </select>
                        <select id="paperSelect" class="template-select">
                            <option value="a4paper">A4 Paper</option>
                            <option value="letterpaper">Letter Paper</option>
                            <option value="legalpaper">Legal Paper</option>
                        </select>
                        <button id="clearBtn" class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i>
                        </button>
                        <div class="ai-controls">
                            <button id="fixWithAI" class="btn btn-sm btn-success">
                                <i class="fas fa-magic"></i> Fix with AI
                            </button>
                            <button id="citationBtn" class="btn btn-sm btn-warning">
                                <i class="fas fa-quote-right"></i> Citation
                            </button>
                        </div>
                    </div>
                </div>
                <textarea id="latexEditor" placeholder="Enter your LaTeX code here...">
\documentclass[a4paper]{article}
\usepackage[utf8]{inputenc}
\usepackage{amsmath}
\usepackage{amsfonts}
\usepackage{amssymb}
\usepackage{graphicx}

\title{My LaTeX Document}
\author{Your Name}
\date{\today}

\begin{document}

\maketitle

\section{Introduction}
Welcome to your LaTeX document! You can write mathematical equations like:

\begin{equation}
E = mc^2
\end{equation}

\section{Features}
\begin{itemize}
    \item Real-time compilation
    \item Professional PDF output
    \item Full LaTeX support
    \item Advanced mathematical typesetting
\end{itemize}

\section{Mathematics}
Here's a more complex equation:

\begin{align}
\frac{\partial f}{\partial x} &= \lim_{h \to 0} \frac{f(x+h) - f(x)}{h} \\
\int_{a}^{b} f(x) dx &= F(b) - F(a)
\end{align}

\end{document}
                </textarea>
                
                <!-- Status Bar -->
                <div class="status-bar">
                    <div class="status-left">
                        <span id="lineCount">Lines: 0</span>
                        <span id="charCount">Characters: 0</span>
                    </div>
                    <div class="status-right">
                        <span id="compileStatus" class="status-ready">Ready</span>
                        <div id="loadingSpinner" class="spinner" style="display: none;"></div>
                    </div>
                </div>
            </div>

            <!-- Resizer -->
            <div class="resizer"></div>

            <!-- Right Panel - PDF Preview -->
            <div class="preview-panel">
                <div class="panel-header">
                    <h3><i class="fas fa-file-pdf"></i> PDF Preview</h3>
                    <div class="preview-tools">
                        <button id="downloadBtn" class="btn btn-sm btn-success" disabled>
                            <i class="fas fa-download"></i> Download PDF
                        </button>
                        <button id="fullscreenBtn" class="btn btn-sm btn-info">
                            <i class="fas fa-expand"></i> Fullscreen
                        </button>
                    </div>
                </div>
                <div class="preview-container">
                    <iframe id="pdfPreview" src="about:blank"></iframe>
                    <div id="errorDisplay" class="error-display" style="display: none;">
                        <div class="error-header">
                            <i class="fas fa-exclamation-triangle"></i>
                            LaTeX Compilation Error
                        </div>
                        <pre id="errorText"></pre>
                    </div>
                    <div id="welcomeMessage" class="welcome-message">
                        <i class="fas fa-rocket"></i>
                        <h3>Welcome to Professional LaTeX Editor!</h3>
                        <p>Start typing your LaTeX code on the left to see the PDF preview here.</p>
                        <p><strong>Features:</strong></p>
                        <ul>
                            <li>✅ Real-time compilation</li>
                            <li>✅ Full LaTeX package support</li>
                            <li>✅ Mathematical equations</li>
                            <li>✅ Professional PDF output</li>
                            <li>✅ Syntax highlighting</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error Toast -->
        <div id="errorToast" class="toast error-toast">
            <div class="toast-content">
                <i class="fas fa-exclamation-circle"></i>
                <span id="toastMessage"></span>
            </div>
            <button class="toast-close">&times;</button>
        </div>

        <!-- Success Toast -->
        <div id="successToast" class="toast success-toast">
            <div class="toast-content">
                <i class="fas fa-check-circle"></i>
                <span id="successMessage"></span>
            </div>
            <button class="toast-close">&times;</button>
        </div>
    </div>

    <!-- Help Popup (Hidden by default) -->
    <div id="helpPopup" class="help-popup" style="display: none;">
        <div class="help-popup-content">
            <h4>Do you want help?</h4>
            <p>I see you encountered some LaTeX errors. Would you like me to show you helpful keyboard shortcuts?</p>
            <div class="help-popup-buttons">
                <button id="helpYesBtn" class="btn btn-primary">Please help me!</button>
                <button id="helpNoBtn" class="btn btn-secondary">No thanks</button>
            </div>
        </div>
    </div>

    <!-- Keyboard Shortcuts Help (Hidden by default) -->
    <div id="keyboardShortcutsHelp" class="keyboard-shortcuts-help" style="display: none;">
        <div class="shortcuts-content">
            <h5>Keyboard Shortcuts</h5>
            <div class="shortcuts-list">
                <div class="shortcut-item">
                    <span class="kbd-hint">Ctrl+S</span>
                    <span>Compile PDF</span>
                </div>
                <div class="shortcut-item">
                    <span class="kbd-hint">Ctrl+Space</span>
                    <span>AI Auto-fix</span>
                </div>
            </div>
            <button id="closeShortcutsBtn" class="close-shortcuts-btn">&times;</button>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>