<?php
/**
 * Enhanced AI Prompts for LaTeX Editor
 * Optimized prompts for better LaTeX assistance
 */

class LaTeXAIPrompts {
    
    /**
     * Enhanced Error Analysis Prompt - Handles any size LaTeX document
     */
    public static function getErrorAnalysisPrompt($latexCode, $errorMessage) {
        $codeLength = strlen($latexCode);
        $lineCount = substr_count($latexCode, "\n") + 1;
        
        return "QWEN 2.5 CODER - LATEX ERROR ANALYSIS & REPAIR

 FAST ANALYSIS REQUIRED - IDENTIFY AND FIX IMMEDIATELY

 DOCUMENT STATISTICS:
- Lines: {$lineCount}
- Characters: {$codeLength}
- Complexity: " . ($codeLength > 5000 ? 'COMPLEX' : ($codeLength > 1000 ? 'MODERATE' : 'SIMPLE')) . "

 ERROR MESSAGE:
{$errorMessage}

 LATEX DOCUMENT:
{$latexCode}

 COMPREHENSIVE ANALYSIS REQUIRED:

1. ERROR TYPE: [Identify exact LaTeX error category]

2. ROOT CAUSE ANALYSIS: [Deep dive into why this error occurred - check document structure, package dependencies, command usage, environment nesting, math expressions, cross-references, bibliography setup, and syntax patterns]

3. ERROR LOCATION: [Pinpoint exact line/section where error originates, including any cascading effects]

4. IMPACT ASSESSMENT: [Explain how this error affects document compilation and what symptoms it causes]

5. COMPREHENSIVE FIX STRATEGY: [Provide detailed, step-by-step repair instructions that address not just the immediate error but also prevent similar issues]

6. CORRECTED CODE:
```latex
[Provide the complete, fully functional LaTeX document with all errors fixed and optimizations applied]
```

🔍 ADVANCED ERROR DETECTION PATTERNS:
• Package Management: Missing, conflicting, or incorrect package usage
• Document Structure: Class options, preamble setup, begin/end document
• Environment Handling: Nested environments, unclosed blocks, incorrect syntax
• Math Mode Issues: Display/inline math mixing, undefined symbols, alignment problems
• Cross-referencing: Labels, citations, references, counters
• Bibliography: BibTeX setup, citation styles, missing entries
• Special Characters: Encoding issues, reserved characters, escape sequences  
• Font and Formatting: Font commands, text styling, spacing issues
• Tables and Figures: Float placement, alignment, caption issues
• Custom Commands: Macro definitions, parameter handling, recursive calls
• File Management: Input files, graphics paths, auxiliary files
• Encoding and Language: UTF-8 issues, babel/polyglossia problems

 SOLUTION REQUIREMENTS:
- Fix ALL identified errors, not just the primary one
- Ensure LaTeX best practices are followed
- Optimize code structure and readability
- Add helpful comments for complex fixes
- Verify package compatibility
- Test logical document flow

 RESPONSE FORMAT:
1. ERROR TYPE: [Specific category]
2. ROOT CAUSE ANALYSIS: [Comprehensive explanation]
3. ERROR LOCATION: [Exact position and context]
4. IMPACT ASSESSMENT: [What this breaks and why]
5. COMPREHENSIVE FIX STRATEGY: [Detailed repair plan]
6. CORRECTED CODE: [Complete working document]";
    }
    
    public static function getSimpleErrorFixPrompt($latexCode, $errorMessage) {
        // For large documents, provide more context but still manageable
        $maxLength = 10000; // Increased to 10000 characters for large documents like resumes
        $shortCode = strlen($latexCode) > $maxLength ? substr($latexCode, 0, $maxLength) . '...' : $latexCode;
        
        // For very long documents, also show the end part which might contain \begin{document}
        if (strlen($latexCode) > $maxLength) {
            $endPart = substr($latexCode, -500); // Last 500 characters
            $shortCode .= "\n\n[... document continues ...]\n\n" . $endPart;
        }
        
        return "Fix this LaTeX error:

ERROR: {$errorMessage}
CODE: {$shortCode}

INSTRUCTIONS:
- If this is a large document, focus on the specific error location
- Don't replace the entire document unless absolutely necessary  
- Provide minimal fix that addresses the error
- If error is about missing packages or commands, suggest additions rather than replacements";
    }
    

    
    /**
     * Document Structure Suggestion
     */
    public static function getStructurePrompt($documentType, $requirements) {
        return "
        You are a LaTeX document structure expert. Create a professional document template.
        
        Document Type: {$documentType}
        Requirements: {$requirements}
        
        Generate a complete LaTeX template with:
        1. Appropriate document class
        2. Essential packages
        3. Professional formatting
        4. Section structure
        5. Best practices comments
        
        Return as JSON:
        {
            \"template\": \"Complete LaTeX code\",
            \"packages_used\": [\"list of packages\"],
            \"features\": [\"list of features included\"],
            \"customization_tips\": [\"tips for customization\"]
        }
        ";
    }
    
    /**
     * Mathematical Expression Helper
     */
    public static function getMathPrompt($mathDescription) {
        return "
        You are a LaTeX mathematical notation expert. Convert this description to LaTeX math.
        
        Description: {$mathDescription}
        
        Provide:
        1. Inline math version (\$...\$)
        2. Display math version (\[...\] or equation environment)
        3. If complex, break into steps
        4. Alternative notations if applicable
        
        Return as JSON:
        {
            \"inline_math\": \"LaTeX inline math code\",
            \"display_math\": \"LaTeX display math code\",
            \"explanation\": \"What each symbol means\",
            \"alternatives\": [\"other ways to write this\"]
        }
        ";
    }
    
    /**
     * Table Generator Prompt
     */
    public static function getTablePrompt($tableData, $styling) {
        return "
        You are a LaTeX table expert. Create a professional table from this data.
        
        Table Data: {$tableData}
        Styling Preferences: {$styling}
        
        Generate:
        1. Basic tabular structure
        2. Professional styling with booktabs
        3. Responsive column widths
        4. Alternative formats (longtable if needed)
        
        Return as JSON:
        {
            \"basic_table\": \"Simple tabular code\",
            \"professional_table\": \"Styled with booktabs\",
            \"packages_needed\": [\"required packages\"],
            \"customization_options\": [\"styling options\"]
        }
        ";
    }
    
    /**
     * Citation and Bibliography Helper
     */
    public static function getBibliographyPrompt($citationStyle, $sources) {
        return "
        You are a LaTeX bibliography expert. Set up citations and bibliography.
        
        Citation Style: {$citationStyle}
        Sources: {$sources}
        
        Provide:
        1. Appropriate bibliography package setup
        2. BibTeX entries for sources
        3. In-text citation examples
        4. Bibliography formatting
        
        Return as JSON:
        {
            \"package_setup\": \"Package loading code\",
            \"bibtex_entries\": \"BibTeX source entries\",
            \"citation_examples\": [\"how to cite in text\"],
            \"bibliography_command\": \"how to generate bibliography\"
        }
        ";
    }
    
    /**
     * Optimization Suggestions
     */
    public static function getOptimizationPrompt($latexCode) {
        return "
        You are a LaTeX optimization expert. Analyze this code for improvements.
        
        LaTeX Code:
        ```latex
        {$latexCode}
        ```
        
        Analyze for:
        1. Performance improvements
        2. Unnecessary packages
        3. Better formatting practices
        4. Code organization
        5. Potential issues
        
        Return as JSON:
        {
            \"performance_tips\": [\"optimization suggestions\"],
            \"package_optimization\": [\"package recommendations\"],
            \"formatting_improvements\": [\"better formatting\"],
            \"code_organization\": [\"structure improvements\"],
            \"warnings\": [\"potential issues to fix\"]
        }
        ";
    }
    
    /**
     * Enhanced Citation Generation Prompt - FAST VERSION
     */
    public static function getCitationPrompt($reference, $style = 'ieee') {
        return "SMART_CITATION: Auto-detect and create BibTeX.

INPUT: {$reference}
STYLE: {$style}

DETECTION PATTERNS:
- arxiv.org/abs/ → @article{arxiv}
- doi.org/ → @article{journal}
- github.com/ → @misc{software}
- \"...\" (book title) → @book{}
- https:// → @misc{website}
- author(year) → @article{paper}

SMART FIELDS:
- Key: lastname+year+firstword
- Title: Extract from URL/text
- Author: Smart name parsing
- Year: Extract from URL/context
- Publisher: Auto-detect from domain

QUICK TEMPLATES:
@article{key, title={{}}, author={{}}, journal={{}}, year={{}}, doi={{}}}
@book{key, title={{}}, author={{}}, publisher={{}}, year={{}}}
@misc{key, title={{}}, author={{}}, url={{}}, year={{}}}

GENERATE:";
    }
    
    /**
     * Bulk Citation Generation Prompt
     */
    public static function getBulkCitationPrompt($references, $style = 'ieee') {
        return "BATCH_CITATION: Process multiple references to BibTeX.

REFERENCES:
{$references}

RULES:
- One BibTeX entry per reference
- Unique keys: author+year+number
- Blank line between entries
- Auto-detect types
- Extract available fields only

FORMAT:
@type{key1, field={value}}

@type{key2, field={value}}

PROCESS:";
    }
    
    /**
     * Smart Citation Key Generator
     */
    public static function getCitationKeyPrompt($title, $authors, $year) {
        return "TASK: Generate a unique, descriptive BibTeX citation key.

INPUT:
- Title: {$title}
- Authors: {$authors}
- Year: {$year}

FORMAT RULES:
- Use format: firstauthorYEARkeyword
- Max 20 characters total
- Use lowercase only
- Remove special characters and spaces
- Use descriptive keyword from title
- Ensure uniqueness

EXAMPLES:
- \"Machine Learning\" by Tom Mitchell, 1997 → mitchell1997machine
- \"Deep Learning\" by Goodfellow et al., 2016 → goodfellow2016deep
- \"Attention Is All You Need\" by Vaswani et al., 2017 → vaswani2017attention

OUTPUT: Return only the citation key, no explanation.";
    }
    
    /**
     * Reference Format Detector
     */
    public static function getReferenceTypePrompt($reference) {
        return "TASK: Detect the type of academic reference and suggest appropriate BibTeX entry type.

INPUT: {$reference}

DETECTION RULES:
- Journal articles: Look for journal names, volume/issue numbers, DOI
- Books: Look for publisher, ISBN, edition information
- Conference papers: Look for conference names, proceedings
- Websites: Look for URLs without academic metadata
- Technical reports: Look for report numbers, institutions
- Theses: Look for university names, degree information

RESPONSE FORMAT:
Return only one of these entry types:
- @article
- @book  
- @inproceedings
- @misc
- @techreport
- @phdthesis
- @mastersthesis
- @inbook
- @incollection

OUTPUT: Return only the BibTeX entry type (e.g., @article).";
    }
    
    /**
     * Citation Context Integration Prompt
     */
    public static function getCitationContextPrompt($reference, $context, $citationStyle = 'ieee') {
        return "ROLE: LaTeX citation specialist with contextual awareness.

TASK: Create a BibTeX entry optimized for the given context and provide LaTeX citation commands.

REFERENCE: {$reference}
CONTEXT: {$context}
STYLE: {$citationStyle}

OUTPUT FORMAT:
1. BibTeX entry (syntactically correct)
2. LaTeX citation commands for the context
3. Brief field explanation if complex

CONTEXT-AWARE FEATURES:
- Academic paper: Include DOI, abstract if relevant
- Technical documentation: Focus on version, URL, access date
- Historical sources: Emphasize year, edition, translator
- Online resources: Include URL, access date, note about permanence

OUTPUT STRUCTURE:
```
% BibTeX Entry
@entrytype{citationkey,
  field1 = {{value1}},
  field2 = {{value2}}
}

% LaTeX Usage
\\cite{citationkey}              % Basic citation
\\textcite{citationkey}          % Textual citation (natbib/biblatex)
\\parencite{citationkey}         % Parenthetical citation (biblatex)
\\citeauthor{citationkey}        % Author only
\\citeyear{citationkey}          % Year only
```

Begin output:";
    }
}

/**
 * Prompt Template Manager
 */
class PromptManager {
    
    private static $templates = [];
    
    /**
     * Register custom prompt template
     */
    public static function registerTemplate($name, $template) {
        self::$templates[$name] = $template;
    }
    
    /**
     * Get prompt by name with variables
     */
    public static function getPrompt($name, $variables = []) {
        if (!isset(self::$templates[$name])) {
            throw new Exception("Prompt template '{$name}' not found");
        }
        
        $template = self::$templates[$name];
        
        // Replace variables in template
        foreach ($variables as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        
        return $template;
    }
    
    /**
     * Initialize default templates
     */
    public static function initialize() {
        // Register all default templates
        $reflection = new ReflectionClass('LaTeXAIPrompts');
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_STATIC);
        
        foreach ($methods as $method) {
            if (strpos($method->getName(), 'get') === 0 && strpos($method->getName(), 'Prompt') !== false) {
                $templateName = strtolower(str_replace(['get', 'Prompt'], '', $method->getName()));
                // Templates are method-based, so we don't pre-register them
            }
        }
    }
}

PromptManager::initialize();
?>