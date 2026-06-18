<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Self-hosted enterprise architecture platform — C4 diagrams, API documentation, integration maps, ADRs, tech radar, collaboration workflows, and multi-format export for your IT landscape.">
    <title>OpenITS | Enterprise Architecture & Integration Platform</title>
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link href="{{ asset('css/openits-public.css') }}" rel="stylesheet">
</head>
<body class="openits-public">

<nav class="openits-nav" id="mainNav">
    <div class="container">
        <a href="{{ url('/') }}" class="brand">
            <img src="{{ asset('landing/assets/img/logo-color.png') }}" alt="OpenITS">
        </a>

        <button class="openits-nav-toggle" id="navToggle" aria-label="Toggle navigation">☰</button>

        <ul class="nav-links" id="navLinks">
            <li><a href="#capabilities">Capabilities</a></li>
            <li><a href="#features">Features</a></li>
            <li><a href="#how-it-works">How It Works</a></li>
            <li><a href="#about">About Us</a></li>
            <li><a href="#contact">Contact Us</a></li>
        </ul>

        <div class="nav-actions" id="navActions">
            @guest
                <a href="https://openits.ir" class="btn-openits btn-openits-ghost" target="_blank" rel="noopener">Live Demo</a>
                <a href="{{ route('login') }}" class="btn-openits btn-openits-ghost">Log In</a>
                <a href="{{ route('register') }}" class="btn-openits btn-openits-primary">Get Started</a>
            @else
                <a href="{{ route('home') }}" class="btn-openits btn-openits-primary">Dashboard</a>
            @endguest
        </div>
    </div>
</nav>

<section class="hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-badge">Enterprise Architecture Platform</div>
            <h1>Your IT landscape. <span>One source of truth.</span></h1>
            <p class="hero-lead">
                Model systems with C4 diagrams. Document APIs and architectural decisions.
                Map integrations, collaborate on changes, govern your tech radar — and export everything from one self-hosted workspace.
            </p>
            <div class="hero-actions">
                @guest
                    <a href="{{ route('register') }}" class="btn-openits btn-openits-primary btn-openits-lg">Start Free</a>
                    <a href="https://openits.ir" class="btn-openits btn-openits-outline btn-openits-lg" target="_blank" rel="noopener">Live Demo</a>
                    <a href="{{ route('login') }}" class="btn-openits btn-openits-outline btn-openits-lg">Log In</a>
                @else
                    <a href="{{ route('home') }}" class="btn-openits btn-openits-primary btn-openits-lg">Go to Dashboard</a>
                @endguest
            </div>
            <div class="hero-protocols">
                @foreach (['REST', 'GraphQL', 'gRPC', 'WebSocket', 'SSE', 'Socket.IO', 'SOAP'] as $protocol)
                    <span class="hero-protocol">{{ $protocol }}</span>
                @endforeach
            </div>
        </div>

        <div class="hero-visual">
            <div class="hero-card">
                <div class="hero-card-header">
                    <span class="dot red"></span>
                    <span class="dot yellow"></span>
                    <span class="dot green"></span>
                    <span>integration-tree</span>
                </div>
                <div class="hero-card-body">
                    <div><span class="tree-vendor">Vendor</span> <span class="tree-line">Salesforce</span></div>
                    <div><span class="tree-line">└──</span> <span class="tree-system">System</span> <span class="tree-line">CRM</span></div>
                    <div><span class="tree-line">    ├──</span> <span class="tree-api">API</span> <span class="tree-line">REST /contacts</span></div>
                    <div><span class="tree-line">    └──</span> <span class="tree-consumer">→</span> <span class="tree-line">Marketing Hub</span></div>
                    <div class="comment"># Vendor → System → API → consumer</div>
                    <div><span class="tree-line">Domain:</span> <span class="tree-system">Enterprise</span> <span class="tree-line">· 12 integrations</span></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="capabilities" class="section section-muted">
    <div class="container">
        <div class="section-header">
            <h2>Model. Document. Visualize. Collaborate.</h2>
            <p>Six capability areas — from interactive C4 diagrams and ADRs to integration maps, change reviews, and multi-format export.</p>
        </div>

        <div class="capabilities-grid">
            <div class="capability-pillar">
                <div class="capability-pillar-icon">🏗️</div>
                <h3>Model with C4</h3>
                <p>Design context, container, and component diagrams per system. Interactive editor with drag-and-drop layout, connect mode, undo/redo, mini-map, and drill-down between C4 levels.</p>
            </div>
            <div class="capability-pillar">
                <div class="capability-pillar-icon">📄</div>
                <h3>Document</h3>
                <p>Catalog REST, GraphQL, gRPC, WebSocket, SOAP, and more — with OpenAPI import and auto-sync into C4. Capture ADRs with status lifecycle and link decisions to architecture elements.</p>
            </div>
            <div class="capability-pillar">
                <div class="capability-pillar-icon">🌐</div>
                <h3>Visualize</h3>
                <p>Explore integration trees (Vendor → System → API → consumer), interactive C4 diagrams, and a technology radar chart across Adopt, Trial, Assess, and Hold rings.</p>
            </div>
            <div class="capability-pillar">
                <div class="capability-pillar-icon">💬</div>
                <h3>Collaborate</h3>
                <p>Comment on diagram elements, submit architecture change requests, and run approval workflows — with version snapshots when changes are accepted.</p>
            </div>
            <div class="capability-pillar">
                <div class="capability-pillar-icon">🏛️</div>
                <h3>Govern</h3>
                <p>Partition by business domain, track per-system technology stacks and server inventory, model BPMN processes, and position technologies on the radar landscape.</p>
            </div>
            <div class="capability-pillar">
                <div class="capability-pillar-icon">📤</div>
                <h3>Import &amp; Export</h3>
                <p>Import OpenAPI, AsyncAPI, Structurizr DSL, and JSON backups into C4 models. Export diagrams as JSON, DSL, Draw.io, SVG, or PNG — plus integration catalogs as CSV or JSON.</p>
            </div>
        </div>
    </div>
</section>

<section id="features" class="section">
    <div class="container">
        <div class="section-header">
            <h2>Everything your integration team needs</h2>
            <p>From landscape modeling to API documentation, process design, and data governance — in one self-hosted platform.</p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">🏢</div>
                <h3>Business Domains</h3>
                <p>Partition your landscape by Enterprise, Marketing, Network, Infrastructure, or custom domains — and see which systems belong where.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🏬</div>
                <h3>Vendors & Systems</h3>
                <p>Model your application landscape hierarchically — vendors own systems, systems can have parent/child relationships.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📄</div>
                <h3>API Documentation</h3>
                <p>Import OpenAPI and WSDL specs automatically. Browse endpoints, parameters, responses, versions, and TPS metrics.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🌳</div>
                <h3>Integration Tree</h3>
                <p>Interactive D3 visualization of how vendors, systems, APIs, and consumer systems connect across your enterprise.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📋</div>
                <h3>Integration Catalog</h3>
                <p>Filterable table of every integration link with CSV and JSON export for governance and compliance workflows.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3>BPMN & Sequence Diagrams</h3>
                <p>Design business processes and API message flows with BPMN models and Mermaid-based sequence diagrams per system.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">⚙️</div>
                <h3>Technology Stack</h3>
                <p>Per-system catalog of languages, frameworks, databases, messaging platforms, and cloud services.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🖥️</div>
                <h3>Infrastructure</h3>
                <p>Server inventory per system — databases, app servers, web tiers, caches, brokers, and load balancers.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🗄️</div>
                <h3>Data Stack</h3>
                <p>Document platform schemas, canonical entities, and field mappings across bronze, silver, and gold data layers.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🏗️</div>
                <h3>Project Management</h3>
                <p>Organize APIs and integrations by project. Keep delivery teams aligned with structured workspaces.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📝</div>
                <h3>Live Markdown Docs</h3>
                <p>Write and edit system documentation with live preview. Auto-generate docs from APIs, integrations, and infrastructure.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔒</div>
                <h3>Secure & Self-Hosted</h3>
                <p>Role-based authentication keeps your architecture documentation private. Deploy on your own infrastructure.</p>
            </div>
        </div>
    </div>
</section>

<section id="how-it-works" class="section section-muted">
    <div class="container">
        <div class="section-header">
            <h2>How it works</h2>
            <p>From empty landscape to exportable catalog — model, document, connect, visualize, and share in five steps.</p>
        </div>

        <div class="steps-grid">
            <div class="step-card">
                <div class="step-number">1</div>
                <h3>Model your landscape</h3>
                <p>Register vendors, business domains, and systems with parent/child hierarchies.</p>
            </div>
            <div class="step-card">
                <div class="step-number">2</div>
                <h3>Document APIs</h3>
                <p>Upload OpenAPI or WSDL files, add tech stacks, servers, and live markdown documentation.</p>
            </div>
            <div class="step-card">
                <div class="step-number">3</div>
                <h3>Connect integrations</h3>
                <p>Link APIs to consumer systems and map how data flows across your enterprise.</p>
            </div>
            <div class="step-card">
                <div class="step-number">4</div>
                <h3>Visualize the tree</h3>
                <p>Explore the interactive Vendor → System → API → consumer integration tree.</p>
            </div>
            <div class="step-card">
                <div class="step-number">5</div>
                <h3>Export your catalog</h3>
                <p>Download integration catalogs and full landscape dumps as CSV or JSON.</p>
            </div>
        </div>
    </div>
</section>

<section id="about" class="section">
    <div class="container">
        <div class="section-header">
            <h2>About Us</h2>
            <p>OpenITS is an open-source platform for enterprise architects, integration teams, and platform engineers.</p>
        </div>

        <div class="about-grid">
            <div class="about-card">
                <h3>Our mission</h3>
                <p>
                    Modern enterprises run dozens of systems connected by REST, GraphQL, gRPC, WebSocket, SOAP, and more —
                    but landscape documentation is often scattered across wikis, spreadsheets, and separate tools.
                    OpenITS brings vendors, domains, systems, APIs, infrastructure, and integrations together
                    in a single, structured, self-hosted workspace.
                </p>
            </div>
            <div class="about-card">
                <h3>What we offer</h3>
                <p>
                    From business domain modeling and vendor hierarchies to integration trees, BPMN processes,
                    technology catalogs, data stack governance, and CSV/JSON export — OpenITS gives architecture
                    and engineering teams a shared source of truth for their integration landscape.
                </p>
            </div>
            <div class="about-card about-founder">
                <h3>Built by Reza Ali</h3>
                <p>
                    OpenITS is created and maintained by
                    <a href="https://rezaalie.ir" target="_blank" rel="noopener"><strong>Reza Ali</strong></a>,
                    focused on making enterprise API documentation practical, accessible, and open source.
                    Whether you are mapping integrations or onboarding a new team member, the goal is simple:
                    less tool switching, more clarity.
                </p>
                <div class="about-links">
                    <a href="https://rezaalie.ir" target="_blank" rel="noopener" class="about-link">rezaalie.ir →</a>
                    <a href="https://github.com/imRezaAlie/openits" target="_blank" rel="noopener" class="about-link">View on GitHub →</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="cta-section">
    <h2>Ready to model your enterprise landscape?</h2>
    <p>Document every protocol, map every integration, and export your catalog — all in one self-hosted workspace.</p>
    @guest
        <a href="{{ route('register') }}" class="btn-openits btn-openits-outline btn-openits-lg">Create Free Account</a>
    @else
        <a href="{{ route('home') }}" class="btn-openits btn-openits-outline btn-openits-lg">Open Dashboard</a>
    @endguest
</section>

<section id="contact" class="section section-muted">
    <div class="container">
        <div class="section-header">
            <h2>Contact Us</h2>
            <p>Questions, feedback, and feature ideas are always welcome — we are actively listening to what would make OpenITS more useful for you.</p>
        </div>

        <div class="contact-layout">
            <div class="contact-info">
                <h3>Get in touch</h3>
                <p>
                    Have an idea for a new feature or improvement? Share it here or on GitHub Issues.
                    Every suggestion helps shape what we build next.
                </p>
                <div class="contact-feature-callout">
                    <strong>Feature requests welcome</strong>
                    <p>Tell us what integrations, workflows, or tools you would like to see in OpenITS.</p>
                </div>
                <ul class="contact-details">
                    <li>
                        <span class="contact-label">Email</span>
                        <a href="mailto:rezaalie70@gmail.com">rezaalie70@gmail.com</a>
                    </li>
                    <li>
                        <span class="contact-label">Maintainer</span>
                        <a href="https://rezaalie.ir" target="_blank" rel="noopener">Reza Ali</a>
                    </li>
                    <li>
                        <span class="contact-label">Website</span>
                        <a href="https://rezaalie.ir" target="_blank" rel="noopener">rezaalie.ir</a>
                    </li>
                    <li>
                        <span class="contact-label">Feature ideas</span>
                        <a href="https://github.com/imRezaAlie/openits/issues" target="_blank" rel="noopener">Submit on GitHub Issues</a>
                    </li>
                </ul>
            </div>

            <div class="contact-form-card">
                @if (session('contact_success'))
                    <div class="contact-alert contact-alert-success" role="alert">
                        {{ session('contact_success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('contact.store') }}" class="contact-form">
                    @csrf

                    <div class="form-group">
                        <label for="contact-name">Your name</label>
                        <input
                            id="contact-name"
                            type="text"
                            name="name"
                            class="form-control-openits @error('name') is-invalid @enderror"
                            value="{{ old('name') }}"
                            required
                            placeholder="Jane Doe"
                        >
                        @error('name')
                            <span class="invalid-feedback" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="contact-email">Email address</label>
                        <input
                            id="contact-email"
                            type="email"
                            name="email"
                            class="form-control-openits @error('email') is-invalid @enderror"
                            value="{{ old('email') }}"
                            required
                            placeholder="you@company.com"
                        >
                        @error('email')
                            <span class="invalid-feedback" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="contact-subject">Subject</label>
                        <input
                            id="contact-subject"
                            type="text"
                            name="subject"
                            class="form-control-openits @error('subject') is-invalid @enderror"
                            value="{{ old('subject') }}"
                            required
                            placeholder="Feature idea, bug report, or general feedback"
                        >
                        @error('subject')
                            <span class="invalid-feedback" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="contact-message">Message</label>
                        <textarea
                            id="contact-message"
                            name="message"
                            rows="5"
                            class="form-control-openits @error('message') is-invalid @enderror"
                            required
                            placeholder="Tell us more about your question or feedback..."
                        >{{ old('message') }}</textarea>
                        @error('message')
                            <span class="invalid-feedback" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="btn-openits btn-openits-primary btn-openits-lg contact-submit">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>

<footer class="openits-footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <div class="footer-brand">OpenITS</div>
                <p>Self-hosted enterprise architecture &amp; integration documentation — model, document, visualize, govern, and export your IT landscape.</p>
            </div>
            <div>
                <h6>Product</h6>
                <ul>
                    <li><a href="#capabilities">Capabilities</a></li>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#how-it-works">How It Works</a></li>
                    @guest
                        <li><a href="{{ route('register') }}">Sign Up</a></li>
                    @endguest
                </ul>
            </div>
            <div>
                <h6>Company</h6>
                <ul>
                    <li><a href="#about">About Us</a></li>
                    <li><a href="#contact">Contact Us</a></li>
                    <li><a href="https://github.com/imRezaAlie/openits/issues" target="_blank" rel="noopener">GitHub Issues</a></li>
                </ul>
            </div>
            <div>
                <h6>Account</h6>
                <ul>
                    <li><a href="{{ route('login') }}">Log In</a></li>
                    <li><a href="{{ route('register') }}">Register</a></li>
                    <li><a href="https://openits.ir" target="_blank" rel="noopener">Live Demo</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <span>&copy; {{ date('Y') }} OpenITS. All rights reserved.</span>
            <span><a href="https://openits.ir" target="_blank" rel="noopener">Live Demo</a> · Open source on <a href="https://github.com/imRezaAlie/openits" target="_blank" rel="noopener">GitHub</a></span>
        </div>
    </div>
</footer>

<script>
    (function () {
        var nav = document.getElementById('mainNav');
        var toggle = document.getElementById('navToggle');
        var links = document.getElementById('navLinks');
        var actions = document.getElementById('navActions');

        window.addEventListener('scroll', function () {
            nav.classList.toggle('scrolled', window.scrollY > 20);
        });

        toggle.addEventListener('click', function () {
            links.classList.toggle('open');
            actions.classList.toggle('open');
        });

        document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
            anchor.addEventListener('click', function (e) {
                var target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth' });
                    links.classList.remove('open');
                    actions.classList.remove('open');
                }
            });
        });

        if (window.location.hash === '#contact') {
            var contactSection = document.getElementById('contact');
            if (contactSection) {
                contactSection.scrollIntoView({ behavior: 'smooth' });
            }
        }
    })();
</script>

</body>
</html>
