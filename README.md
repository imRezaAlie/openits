<div align="center">

<img src="public/landing/assets/img/logo-color.png" alt="OpenITS Logo" width="240"/>

# OpenITS

**Open-source enterprise architecture & integration documentation platform**

Model your IT landscape, document multi-protocol APIs, map cross-system integrations, and visualize how business domains connect — in one self-hosted workspace.

<br/>

<img src="public/readme/hero-platform.svg" alt="OpenITS platform overview" width="720"/>

<br/>

[![License: Apache 2.0](https://img.shields.io/badge/License-Apache_2.0-blue.svg?style=flat-square)](LICENSE)
[![Live Demo](https://img.shields.io/badge/Live_Demo-openits.ir-4f46e5?style=flat-square&logo=googlechrome&logoColor=white)](https://openits.ir)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com/)
[![MySQL](https://img.shields.io/badge/MySQL-8%2B-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![SQLite](https://img.shields.io/badge/SQLite-supported-003B57?style=flat-square&logo=sqlite&logoColor=white)](https://www.sqlite.org/)

<br/>

[![REST](https://img.shields.io/badge/REST-✓-4f46e5?style=flat-square)]()
[![GraphQL](https://img.shields.io/badge/GraphQL-✓-E10098?style=flat-square&logo=graphql&logoColor=white)]()
[![gRPC](https://img.shields.io/badge/gRPC-✓-244c5a?style=flat-square)]()
[![WebSocket](https://img.shields.io/badge/WebSocket-✓-010101?style=flat-square)]()
[![SOAP](https://img.shields.io/badge/SOAP-✓-005A9C?style=flat-square)]()
[![SSE](https://img.shields.io/badge/SSE-✓-06b6d4?style=flat-square)]()
[![Socket.IO](https://img.shields.io/badge/Socket.IO-✓-010101?style=flat-square&logo=socketdotio&logoColor=white)]()

<br/>

[Features](#features) ·
[Live Demo](https://openits.ir) ·
[How It Works](#how-it-works) ·
[Quick Start](#quick-start) ·
[Architecture](#architecture-model) ·
[Logo & Brand](#logo--brand-assets) ·
[Contributing](#contributing) ·
[License](#license)

</div>

---

## About

**OpenITS** is a self-hosted platform for enterprise architects, integration teams, and platform engineers who need a single source of truth for their application landscape.

<table align="center">
  <tr>
    <td align="center" width="200">
      <img src="public/readme/feature-document.svg" alt="Document" width="64"/><br/>
      <b>Document</b><br/>
      <sub>REST, SOAP, GraphQL, gRPC, WebSocket, SSE & more</sub>
    </td>
    <td align="center" width="200">
      <img src="public/readme/feature-visualize.svg" alt="Visualize" width="64"/><br/>
      <b>Visualize</b><br/>
      <sub>Vendor → System → API → consumer maps</sub>
    </td>
    <td align="center" width="200">
      <img src="public/readme/feature-govern.svg" alt="Govern" width="64"/><br/>
      <b>Govern</b><br/>
      <sub>Domains, tech stacks, BPMN & infrastructure</sub>
    </td>
    <td align="center" width="200">
      <img src="public/readme/feature-export.svg" alt="Export" width="64"/><br/>
      <b>Export</b><br/>
      <sub>CSV, JSON & full landscape dumps</sub>
    </td>
  </tr>
</table>

---

## How it works

```mermaid
flowchart LR
    A[🏢 Register Systems] --> B[📋 Document APIs]
    B --> C[🔗 Map Integrations]
    C --> D[🌳 Visualize Tree]
    D --> E[📤 Export Catalog]

    style A fill:#eef2ff,stroke:#4f46e5
    style B fill:#ecfeff,stroke:#06b6d4
    style C fill:#f0fdf4,stroke:#22c55e
    style D fill:#fef3c7,stroke:#f59e0b
    style E fill:#fce7f3,stroke:#ec4899
```

<table align="center">
  <tr>
    <td align="center" width="120">
      <img src="public/readme/workflow-model.svg" alt="Model" width="56"/><br/>
      <b>1 · Model</b><br/>
      <sub>Domains &amp; systems</sub>
    </td>
    <td align="center" width="28">→</td>
    <td align="center" width="120">
      <img src="public/readme/feature-document.svg" alt="Document" width="56"/><br/>
      <b>2 · Document</b><br/>
      <sub>APIs &amp; protocols</sub>
    </td>
    <td align="center" width="28">→</td>
    <td align="center" width="120">
      <img src="public/readme/workflow-connect.svg" alt="Connect" width="56"/><br/>
      <b>3 · Connect</b><br/>
      <sub>Integration links</sub>
    </td>
    <td align="center" width="28">→</td>
    <td align="center" width="120">
      <img src="public/readme/workflow-visualize.svg" alt="Visualize" width="56"/><br/>
      <b>4 · Visualize</b><br/>
      <sub>Vendor → API tree</sub>
    </td>
    <td align="center" width="28">→</td>
    <td align="center" width="120">
      <img src="public/readme/feature-export.svg" alt="Export" width="56"/><br/>
      <b>5 · Export</b><br/>
      <sub>Catalog &amp; dumps</sub>
    </td>
  </tr>
</table>

---

## Features

<table>
  <tr>
    <td width="50%" valign="top">
      <img src="public/readme/feature-domains.svg" align="left" width="40" hspace="8" alt="Business domains"/>
      <b>Business domains</b><br/>
      Partition the landscape — Enterprise, Marketing, Network, Infrastructure, or custom domains.
    </td>
    <td width="50%" valign="top">
      <img src="public/readme/workflow-model.svg" align="left" width="40" hspace="8" alt="Vendors and systems"/>
      <b>Vendors & systems</b><br/>
      Hierarchical application landscape with parent/child system relationships.
    </td>
  </tr>
  <tr>
    <td valign="top">
      <img src="public/readme/feature-document.svg" align="left" width="40" hspace="8" alt="API documentation"/>
      <b>API & integration docs</b><br/>
      REST, SOAP, GraphQL, gRPC, WebSocket, SSE, Socket.IO, SFTP, FTPS, Zabbix, SIEM, Splunk.
    </td>
    <td valign="top">
      <img src="public/readme/feature-visualize.svg" align="left" width="40" hspace="8" alt="Integration tree"/>
      <b>Integration tree</b><br/>
      Interactive D3 visualization: Vendor → System → API → consumer systems.
    </td>
  </tr>
  <tr>
    <td valign="top">
      <img src="public/readme/feature-catalog.svg" align="left" width="40" hspace="8" alt="Integration catalog"/>
      <b>Integration catalog</b><br/>
      Filterable table of all integration links with CSV/JSON export.
    </td>
    <td valign="top">
      <img src="public/readme/feature-bpmn.svg" align="left" width="40" hspace="8" alt="BPMN diagrams"/>
      <b>BPMN & sequence diagrams</b><br/>
      Process models and Mermaid-based API/integration message flow designer.
    </td>
  </tr>
  <tr>
    <td valign="top">
      <img src="public/readme/feature-tech.svg" align="left" width="40" hspace="8" alt="Technology stack"/>
      <b>Technology stack</b><br/>
      Per-system catalog — languages, frameworks, databases, messaging, cloud.
    </td>
    <td valign="top">
      <img src="public/readme/feature-infrastructure.svg" align="left" width="40" hspace="8" alt="Infrastructure"/>
      <b>Infrastructure</b><br/>
      Server inventory per system — DB, app, web, cache, brokers, load balancers.
    </td>
  </tr>
</table>

---

## Architecture model

```mermaid
graph TB
    subgraph Landscape["🏗️ Enterprise Landscape"]
        V[Vendor]
        D[Domain]
        V --> S[System]
        D --> S
        S --> S2[Child System]
    end

    subgraph SystemDetail["📦 System Detail"]
        S --> API[APIs]
        S --> BPMN[BPMN Processes]
        S --> TECH[Technologies]
        S --> SRV[Servers]
        API --> CS[Consumer Systems]
    end

    style V fill:#4f46e5,color:#fff
    style D fill:#06b6d4,color:#fff
    style S fill:#22c55e,color:#fff
    style API fill:#f59e0b,color:#fff
```

```mermaid
erDiagram
    DOMAIN ||--o{ SYSTEM : contains
    VENDOR ||--o{ SYSTEM : owns
    SYSTEM ||--o{ SYSTEM : "parent/child"
    SYSTEM ||--o{ API : publishes
    API ||--o{ SYSTEM : "consumed by"
    SYSTEM ||--o{ BPMN : models
    SYSTEM ||--o{ SERVER : hosts
    SYSTEM }o--o{ TECHNOLOGY : uses
```

<details>
<summary><b>Text representation</b></summary>

```
Vendor
  └── System (domain, parent/child hierarchy)
        ├── APIs (owner_system_id)
        │     └── consumer systems (api_system pivot)
        ├── BPMN process / Sequence diagram
        ├── technologies (pivot)
        └── servers

Domain
  └── systems
```

</details>

---

## Integration flow

```mermaid
sequenceDiagram
    participant EA as Enterprise Architect
    participant OI as OpenITS
    participant DB as Database
    participant EA2 as Downstream EA Tool

    EA->>OI: Register domains & systems
    EA->>OI: Document APIs & integrations
    OI->>DB: Persist landscape model
    EA->>OI: Explore integration tree
    OI-->>EA: D3 visualization
    EA->>OI: Export catalog / landscape JSON
    OI-->>EA2: CSV / JSON feed
```

---

## Requirements

<table align="center">
  <tr>
    <td align="center"><img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP"/></td>
    <td align="center"><img src="https://img.shields.io/badge/Composer-required-885630?style=for-the-badge&logo=composer&logoColor=white" alt="Composer"/></td>
    <td align="center"><img src="https://img.shields.io/badge/MySQL-8+-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL"/></td>
    <td align="center"><img src="https://img.shields.io/badge/SQLite-supported-003B57?style=for-the-badge&logo=sqlite&logoColor=white" alt="SQLite"/></td>
    <td align="center"><img src="https://img.shields.io/badge/Node.js-18+-339933?style=for-the-badge&logo=nodedotjs&logoColor=white" alt="Node.js"/></td>
  </tr>
</table>

| Dependency | Version |
|------------|---------|
| PHP | 8.2 or higher |
| Composer | Latest stable |
| Database | MySQL 8+ or SQLite |
| Node.js | 18+ *(optional, for Vite asset builds)* |

---

## Quick start

> **Live demo:** [https://openits.ir](https://openits.ir)

```mermaid
flowchart TD
    A["git clone"] --> B["composer install"]
    B --> C["cp .env.example .env"]
    C --> D["php artisan key:generate"]
    D --> E["php artisan migrate --seed"]
    E --> F["php artisan serve"]
    F --> G["Open localhost:8000"]

    style A fill:#eef2ff,stroke:#4f46e5
    style G fill:#dcfce7,stroke:#22c55e
```

### 1. Clone & install

```bash
git clone https://github.com/imRezaAlie/openits.git
cd openits
composer install
```

### 2. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=openits
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Migrate & seed

```bash
php artisan migrate
php artisan db:seed
```

### 4. Run

```bash
php artisan serve
```

Open **[http://localhost:8000](http://localhost:8000)**, register at `/register`, then sign in to access the dashboard.

### Frontend assets *(optional)*

```bash
npm install
npm run build   # production build
npm run dev     # development with hot reload
```

---

## Demo data

Seeders populate a realistic enterprise scenario — Salesforce, SAP, Stripe, multi-protocol APIs, and cross-domain integrations:

```bash
php artisan db:seed
```

---

## Key routes

```mermaid
mindmap
  root((OpenITS))
    Dashboard
      /home
    Landscape
      /domains
      /systems
    Integrations
      /apis
      /integrations/tree
      /integrations/catalog
    Governance
      /processes
      /technologies
      /infrastructure
    Export
      /integrations/export
```

| Feature | Route |
|---------|-------|
| Dashboard | `/home` |
| Domains | `/domains` |
| Systems | `/systems` |
| API documentation | `/apis` |
| Integration tree | `/integrations/tree` |
| Integration catalog | `/integrations/catalog` |
| Full landscape export (JSON) | `/integrations/export` |
| BPMN processes | `/processes` |
| Technologies | `/technologies` |
| Infrastructure | `/infrastructure` |

> All application routes require authentication except the landing page and auth screens.

---

## Export & integration

```mermaid
flowchart LR
    OI[OpenITS] --> CAT["Integration Catalog<br/>CSV / JSON"]
    OI --> LAND["Full EA Landscape<br/>JSON"]
    CAT --> RPT[Reporting]
    CAT --> GOV[Governance Reviews]
    LAND --> EA[Downstream EA Tools]

    style OI fill:#4f46e5,color:#fff
    style CAT fill:#06b6d4,color:#fff
    style LAND fill:#22c55e,color:#fff
```

| Export | Endpoint | Format |
|--------|----------|--------|
| Integration catalog | `/integrations/catalog/export` | CSV / JSON |
| Full EA landscape | `/integrations/export` | JSON |

---

## Tech stack

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel"/>
  <img src="https://img.shields.io/badge/Eloquent-ORM-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Eloquent"/>
  <img src="https://img.shields.io/badge/Blade-templates-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Blade"/>
  <img src="https://img.shields.io/badge/Bootstrap-5-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white" alt="Bootstrap"/>
  <img src="https://img.shields.io/badge/D3.js-visualization-F9A03C?style=for-the-badge&logo=d3dotjs&logoColor=white" alt="D3.js"/>
  <img src="https://img.shields.io/badge/BPMN.js-editor-FF6B6B?style=for-the-badge" alt="BPMN.js"/>
  <img src="https://img.shields.io/badge/Swagger_UI-OpenAPI-85EA2D?style=for-the-badge&logo=swagger&logoColor=black" alt="Swagger UI"/>
  <img src="https://img.shields.io/badge/Mermaid-diagrams-FF3670?style=for-the-badge&logo=mermaid&logoColor=white" alt="Mermaid"/>
</p>

| Layer | Technologies |
|-------|--------------|
| **Backend** | Laravel 11, Eloquent, Blade |
| **UI** | Bootstrap admin theme (Deznav) |
| **Visualization** | D3.js, BPMN.js, Swagger UI, Mermaid |

---

## Logo & brand assets

Official OpenITS logos are included in the repository for use in documentation, presentations, and integrations.

<table align="center">
  <tr>
    <td align="center" width="280">
      <img src="public/landing/assets/img/logo-color.png" alt="OpenITS — color logo" width="180"/>
      <br/><sub><b>Color</b> — light backgrounds</sub>
    </td>
    <td align="center" width="280" bgcolor="#1e293b">
      <img src="public/readme/logo-white.svg" alt="OpenITS — white logo" width="180"/>
      <br/><sub><b>White</b> — dark backgrounds</sub>
    </td>
  </tr>
  <tr>
    <td align="center" colspan="2">
      <br/>
      <img src="public/images/logo/logo-full.png" alt="OpenITS — full logo" width="260"/>
      <br/><sub><b>Full logo</b> — admin sidebar & print layouts</sub>
    </td>
  </tr>
</table>

| Asset | Path | Usage |
|-------|------|-------|
| Color logo | `public/landing/assets/img/logo-color.png` | Light backgrounds, README, docs |
| White logo | `public/readme/logo-white.svg` | Dark backgrounds, README |
| Full logo | `public/images/logo/logo-full.png` | Admin sidebar, print layouts |
| Compact logo | `public/images/small-logo.png` | Navbar, favicons, tight spaces |
| Favicon | `public/images/favicon.png` | Browser tab icon |
| README icons | `public/readme/*.svg` | Feature & workflow illustrations |

When referencing OpenITS in external materials, please use the **color logo** on light backgrounds and the **white logo** on dark backgrounds. Do not stretch, recolor, or modify the logo proportions.

---

## Contributing

```mermaid
gitGraph
   commit id: "Fork repo"
   branch feature
   checkout feature
   commit id: "Add feature"
   commit id: "Write tests"
   checkout main
   merge feature id: "Pull Request"
```

Contributions are welcome! Please open an issue to discuss significant changes before submitting a pull request.

1. **Fork** the repository
2. **Create** a feature branch (`git checkout -b feature/my-feature`)
3. **Commit** your changes (`git commit -m 'Add my feature'`)
4. **Push** to the branch (`git push origin feature/my-feature`)
5. **Open** a Pull Request

---

## License

OpenITS is open-source software licensed under the **[Apache License 2.0](LICENSE)**.

Copyright © 2026 Reza Alie

---

## Author

**Reza Alie**

- **Demo:** [openits.ir](https://openits.ir)
- **Website:** [rezaalie.ir](https://rezaalie.ir)
- **Email:** rezaalie70[at]gmail.com
- **LinkedIn:** [linkedin.com/in/rezaalie](https://www.linkedin.com/in/rezaalie)

---

<div align="center">

<img src="public/landing/assets/img/logo-color.png" alt="OpenITS" width="120"/>

<br/><br/>

**[⬆ Back to top](#openits)**

<br/>

[Live Demo](https://openits.ir) · [Report a bug](https://github.com/imRezaAlie/openits/issues) · [Request a feature](https://github.com/imRezaAlie/openits/issues) · [Discussions](https://github.com/imRezaAlie/openits/discussions)

<br/>

[Website](https://rezaalie.ir) · [LinkedIn](https://www.linkedin.com/in/rezaalie) · rezaalie70[at]gmail.com

</div>
