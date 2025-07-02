<?php
/**
 * Smart LaTeX Templates
 * Pre-built templates for common document types
 */

class SmartLaTeXTemplates {
    
    /**
     * Academic Paper Template
     */
    public static function getAcademicPaper() {
        return [
            'name' => 'Academic Paper',
            'description' => 'Professional academic paper with IEEE style',
            'template' => '
\documentclass[conference]{IEEEtran}

% Essential packages
\usepackage[utf8]{inputenc}
\usepackage{amsmath,amsfonts,amssymb}
\usepackage{graphicx}
\usepackage{cite}
\usepackage{url}
\usepackage{hyperref}

% Title and author
\title{Your Paper Title Here}
\author{
    \IEEEauthorblockN{Your Name}
    \IEEEauthorblockA{Your Institution\\
    Department Name\\
    City, Country\\
    Email: your.email@institution.edu}
}

\begin{document}

\maketitle

\begin{abstract}
Write your abstract here. This should be a concise summary of your paper in 150-250 words.
\end{abstract}

\begin{IEEEkeywords}
keyword1, keyword2, keyword3, keyword4
\end{IEEEkeywords}

\section{Introduction}
Your introduction goes here. Explain the problem, motivation, and your contributions.

\section{Related Work}
Discuss previous work in this area and how your work differs.

\section{Methodology}
Describe your approach and methods.

\section{Results}
Present your results with figures and tables.

\begin{figure}[htbp]
\centering
% \includegraphics[width=0.8\columnwidth]{figure1.png}
\caption{Caption for your figure}
\label{fig:figure1}
\end{figure}

\section{Conclusion}
Summarize your findings and future work.

\section{Acknowledgments}
Thank people who helped with your research.

\bibliographystyle{IEEEtran}
\bibliography{references}

\end{document}
            ',
            'packages' => ['IEEEtran', 'amsmath', 'graphicx', 'cite', 'hyperref'],
            'features' => ['IEEE format', 'Bibliography', 'Figures', 'Abstract', 'Keywords']
        ];
    }
    
    /**
     * Resume Template
     */
    public static function getResume() {
        return [
            'name' => 'Professional Resume',
            'description' => 'Clean, modern resume template',
            'template' => '
\documentclass[letterpaper,11pt]{article}

% Packages
\usepackage{latexsym}
\usepackage[empty]{fullpage}
\usepackage{titlesec}
\usepackage{marvosym}
\usepackage[usenames,dvipsnames]{color}
\usepackage{verbatim}
\usepackage{enumitem}
\usepackage[hidelinks]{hyperref}
\usepackage{fancyhdr}
\usepackage[english]{babel}
\usepackage{tabularx}

% Page setup
\pagestyle{fancy}
\fancyhf{}
\fancyfoot{}
\renewcommand{\headrulewidth}{0pt}
\renewcommand{\footrulewidth}{0pt}

% Margins
\addtolength{\oddsidemargin}{-0.5in}
\addtolength{\evensidemargin}{-0.5in}
\addtolength{\textwidth}{1in}
\addtolength{\topmargin}{-.5in}
\addtolength{\textheight}{1.0in}

% Custom commands
\newcommand{\resumeItem}[1]{\item\small{#1}}
\newcommand{\resumeSubheading}[4]{
  \begin{tabular*}{0.97\textwidth}[t]{l@{\extracolsep{\fill}}r}
    \textbf{#1} & #2 \\
    \textit{\small#3} & \textit{\small #4} \\
  \end{tabular*}\vspace{-7pt}
}

\begin{document}

% Header
\begin{center}
    \textbf{\Huge \scshape Your Name} \\ \vspace{1pt}
    \small +1-123-456-7890 $|$ \href{mailto:your.email@example.com}{\underline{your.email@example.com}} $|$ 
    \href{https://linkedin.com/in/yourprofile}{\underline{linkedin.com/in/yourprofile}} $|$
    \href{https://github.com/yourusername}{\underline{github.com/yourusername}}
\end{center}

% Education
\section{Education}
\resumeSubheading
{University Name}{City, State}
{Bachelor of Science in Computer Science}{Expected May 2024}

% Experience
\section{Experience}
\resumeSubheading
{Software Engineer Intern}{Summer 2023}
{Company Name}{City, State}
\begin{itemize}[leftmargin=0.15in, label={}]
    \resumeItem{Developed web applications using React and Node.js}
    \resumeItem{Collaborated with team of 5 developers using Agile methodology}
    \resumeItem{Improved application performance by 25\% through code optimization}
\end{itemize}

% Projects
\section{Projects}
\resumeSubheading
{Project Name}{\href{https://github.com/project}{\underline{github.com/project}}}
{Technologies Used}{Date}
\begin{itemize}[leftmargin=0.15in, label={}]
    \resumeItem{Brief description of what the project does}
    \resumeItem{Key technologies and achievements}
\end{itemize}

% Skills
\section{Technical Skills}
\begin{itemize}[leftmargin=0.15in, label={}]
    \item{\textbf{Languages}{: Python, JavaScript, Java, C++, SQL}}
    \item{\textbf{Frameworks}{: React, Node.js, Django, Flask}}
    \item{\textbf{Tools}{: Git, Docker, AWS, Linux}}
\end{itemize}

\end{document}
            ',
            'packages' => ['fullpage', 'titlesec', 'hyperref', 'enumitem'],
            'features' => ['Professional layout', 'Contact links', 'Sections', 'Clean design']
        ];
    }
    
    /**
     * Presentation Template (Beamer)
     */
    public static function getPresentation() {
        return [
            'name' => 'Professional Presentation',
            'description' => 'Beamer presentation with modern theme',
            'template' => '
\documentclass{beamer}

% Theme
\usetheme{Madrid}
\usecolortheme{default}

% Packages
\usepackage[utf8]{inputenc}
\usepackage{amsmath,amsfonts,amssymb}
\usepackage{graphicx}
\usepackage{booktabs}

% Title page info
\title{Your Presentation Title}
\subtitle{Subtitle if needed}
\author{Your Name}
\institute{Your Institution}
\date{\today}

\begin{document}

% Title slide
\frame{\titlepage}

% Outline
\begin{frame}
    \frametitle{Outline}
    \tableofcontents
\end{frame}

% Section 1
\section{Introduction}
\begin{frame}
    \frametitle{Introduction}
    \begin{itemize}
        \item Point 1
        \item Point 2
        \item Point 3
    \end{itemize}
\end{frame}

% Section 2
\section{Main Content}
\begin{frame}
    \frametitle{Main Content}
    \begin{columns}
        \begin{column}{0.5\textwidth}
            \begin{itemize}
                \item Left column content
                \item More points
            \end{itemize}
        \end{column}
        \begin{column}{0.5\textwidth}
            \begin{figure}
                % \includegraphics[width=\textwidth]{image.png}
                \caption{Your figure caption}
            \end{figure}
        \end{column}
    \end{columns}
\end{frame}

% Math example
\begin{frame}
    \frametitle{Mathematical Content}
    \begin{equation}
        E = mc^2
    \end{equation}
    
    \begin{align}
        f(x) &= ax^2 + bx + c \\
        f\'(x) &= 2ax + b
    \end{align}
\end{frame}

% Conclusion
\section{Conclusion}
\begin{frame}
    \frametitle{Conclusion}
    \begin{itemize}
        \item Summary point 1
        \item Summary point 2
        \item Future work
    \end{itemize}
\end{frame}

% Thank you slide
\begin{frame}
    \frametitle{Thank You}
    \begin{center}
        \Large Thank you for your attention!
        
        \vspace{1cm}
        
        Questions?
    \end{center}
\end{frame}

\end{document}
            ',
            'packages' => ['beamer', 'amsmath', 'graphicx', 'booktabs'],
            'features' => ['Modern theme', 'Outline', 'Columns', 'Math support', 'Professional layout']
        ];
    }
    
    /**
     * Report Template
     */
    public static function getReport() {
        return [
            'name' => 'Technical Report',
            'description' => 'Comprehensive report template',
            'template' => '
\documentclass[12pt,a4paper]{report}

% Packages
\usepackage[utf8]{inputenc}
\usepackage[margin=1in]{geometry}
\usepackage{amsmath,amsfonts,amssymb}
\usepackage{graphicx}
\usepackage{booktabs}
\usepackage{hyperref}
\usepackage{listings}
\usepackage{xcolor}
\usepackage{fancyhdr}
\usepackage{tocloft}

% Page style
\pagestyle{fancy}
\fancyhf{}
\fancyhead[L]{\leftmark}
\fancyhead[R]{\thepage}
\renewcommand{\headrulewidth}{0.4pt}

% Code listing style
\lstset{
    basicstyle=\ttfamily\small,
    keywordstyle=\color{blue},
    commentstyle=\color{green},
    stringstyle=\color{red},
    showstringspaces=false,
    breaklines=true,
    frame=single,
    backgroundcolor=\color{gray!10}
}

% Title page
\title{
    \vspace{2in}
    \textbf{\huge Report Title} \\
    \vspace{0.5in}
    \large Subtitle if needed \\
    \vspace{1in}
}
\author{
    \textbf{Author Name} \\
    Student ID: 123456789 \\
    \vspace{0.5in} \\
    Department Name \\
    University Name \\
    \vspace{1in}
}
\date{\today}

\begin{document}

% Title page
\maketitle
\thispagestyle{empty}
\newpage

% Table of contents
\tableofcontents
\newpage

% List of figures
\listoffigures
\newpage

% List of tables
\listoftables
\newpage

% Abstract
\begin{abstract}
Write your abstract here. This should summarize the entire report in 200-300 words.
\end{abstract}
\newpage

% Main content
\chapter{Introduction}
\section{Background}
Provide background information about your topic.

\section{Objectives}
List your objectives:
\begin{enumerate}
    \item Objective 1
    \item Objective 2
    \item Objective 3
\end{enumerate}

\chapter{Literature Review}
Review relevant literature and previous work.

\chapter{Methodology}
\section{Approach}
Describe your methodology.

\section{Tools and Technologies}
List tools used:
\begin{itemize}
    \item Tool 1
    \item Tool 2
    \item Tool 3
\end{itemize}

\chapter{Results and Analysis}
\section{Results}
Present your results.

\begin{figure}[htbp]
    \centering
    % \includegraphics[width=0.8\textwidth]{result_graph.png}
    \caption{Results visualization}
    \label{fig:results}
\end{figure}

\begin{table}[htbp]
    \centering
    \begin{tabular}{@{}lcc@{}}
    \toprule
    Parameter & Value 1 & Value 2 \\
    \midrule
    Item 1 & 10.5 & 12.3 \\
    Item 2 & 8.7 & 9.1 \\
    Item 3 & 15.2 & 14.8 \\
    \bottomrule
    \end{tabular}
    \caption{Results summary}
    \label{tab:results}
\end{table}

\section{Analysis}
Analyze your results.

\chapter{Conclusion}
\section{Summary}
Summarize your findings.

\section{Future Work}
Discuss potential future work.

% Bibliography
\begin{thebibliography}{99}
\bibitem{ref1} Author, A. (2023). \textit{Paper Title}. Journal Name, 15(3), 123-145.
\bibitem{ref2} Author, B. (2022). \textit{Book Title}. Publisher.
\end{thebibliography}

% Appendices
\appendix
\chapter{Additional Data}
Include additional data, code, or supplementary material here.

\end{document}
            ',
            'packages' => ['geometry', 'amsmath', 'graphicx', 'booktabs', 'hyperref', 'listings', 'fancyhdr'],
            'features' => ['Title page', 'TOC', 'Lists', 'Code listings', 'Professional formatting']
        ];
    }
    
    /**
     * Get all available templates
     */
    public static function getAllTemplates() {
        return [
            'academic' => self::getAcademicPaper(),
            'resume' => self::getResume(),
            'presentation' => self::getPresentation(),
            'report' => self::getReport()
        ];
    }
    
    /**
     * Get template by name
     */
    public static function getTemplate($name) {
        $templates = self::getAllTemplates();
        return isset($templates[$name]) ? $templates[$name] : null;
    }
}
?>