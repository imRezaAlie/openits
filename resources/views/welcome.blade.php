<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="REST, GraphQL, gRPC, WebSocket, SSE, Socket.IO, SOAP — design, debug, test, and document all your APIs in a single OpenITS workspace.">
    <title>OpenITS | Unified API Workspace</title>
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
            <div class="hero-badge">Unified API Workspace</div>
            <h1>Every protocol. <span>One workspace.</span></h1>
            <p class="hero-lead">
                REST, GraphQL, gRPC, WebSocket, SSE, Socket.IO, SOAP — design, debug, test, and document
                all your APIs in a single workspace. No more switching between tools for each protocol.
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
                    <span>api-spec.yaml</span>
                </div>
                <div class="hero-card-body">
                    <div><span class="method">GET</span> <span class="path">/api/v1/users</span></div>
                    <div><span class="method">POST</span> <span class="path">/api/v1/users</span></div>
                    <div><span class="method">GET</span> <span class="path">/api/v1/systems</span></div>
                    <div class="comment"># Auto-generated from OpenAPI 3.0</div>
                    <div><span class="method">PUT</span> <span class="path">/api/v1/integrations</span></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="features" class="section">
    <div class="container">
        <div class="section-header">
            <h2>One workspace for every API protocol</h2>
            <p>Design, debug, test, and document REST, GraphQL, gRPC, WebSocket, SSE, Socket.IO, and SOAP — without juggling separate tools.</p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">📄</div>
                <h3>API Documentation</h3>
                <p>Import OpenAPI and SOAP specs automatically. Browse endpoints, parameters, and responses in a clean interface.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔗</div>
                <h3>Integration Mapping</h3>
                <p>Visualize how systems connect. Map APIs to systems and understand dependencies at a glance.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🏗️</div>
                <h3>Project Management</h3>
                <p>Organize APIs and integrations by project. Keep teams aligned with structured workspaces.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3>BPMN Modeling</h3>
                <p>Design and document business processes alongside your API catalog for end-to-end visibility.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">⚡</div>
                <h3>TPS Tracking</h3>
                <p>Record transactions-per-second metrics for each API to support capacity planning and SLAs.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📝</div>
                <h3>Live Markdown Documentation</h3>
                <p>Write and edit system documentation in markdown with a live preview. Auto-generate docs from APIs, integrations, and infrastructure.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔒</div>
                <h3>Secure Access</h3>
                <p>Role-based authentication keeps your API documentation private and accessible only to your team.</p>
            </div>
        </div>
    </div>
</section>

<section id="how-it-works" class="section section-muted">
    <div class="container">
        <div class="section-header">
            <h2>How it works</h2>
            <p>Get started in minutes — import your first API spec and explore your integration landscape.</p>
        </div>

        <div class="steps-grid">
            <div class="step-card">
                <div class="step-number">1</div>
                <h3>Create an account</h3>
                <p>Sign up and access your OpenITS workspace instantly.</p>
            </div>
            <div class="step-card">
                <div class="step-number">2</div>
                <h3>Import your APIs</h3>
                <p>Upload OpenAPI or WSDL files to generate interactive documentation.</p>
            </div>
            <div class="step-card">
                <div class="step-number">3</div>
                <h3>Map integrations</h3>
                <p>Connect APIs to systems and visualize your integration tree.</p>
            </div>
            <div class="step-card">
                <div class="step-number">4</div>
                <h3>Share with your team</h3>
                <p>Keep everyone on the same page with always-current API docs.</p>
            </div>
        </div>
    </div>
</section>

<section id="about" class="section">
    <div class="container">
        <div class="section-header">
            <h2>About Us</h2>
            <p>OpenITS is built to help teams design, debug, test, and document every API protocol in one place.</p>
        </div>

        <div class="about-grid">
            <div class="about-card">
                <h3>Our mission</h3>
                <p>
                    Modern enterprises rely on REST, GraphQL, gRPC, WebSocket, SSE, Socket.IO, SOAP, and more —
                    but documentation and integration tooling are often scattered across separate products.
                    OpenITS brings everything together in a single, structured workspace.
                </p>
            </div>
            <div class="about-card">
                <h3>What we offer</h3>
                <p>
                    From API catalogs and integration mapping to BPMN modeling, TPS tracking, and live markdown
                    documentation, OpenITS gives architecture and engineering teams a shared source of truth for
                    their integration landscape.
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
    <h2>Ready to unify your API workflow?</h2>
    <p>Design, debug, test, and document every protocol in one place — no more tool switching.</p>
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
                <p>Design, debug, test, and document all your APIs in a single workspace — across every protocol.</p>
            </div>
            <div>
                <h6>Product</h6>
                <ul>
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
