# OpenITS Roadmap

This document describes what OpenITS intends to do and **not do** over the next **12 months** (June 2026 – June 2027). It is updated by maintainers as priorities change. Discuss proposals via [GitHub Issues](https://github.com/imRezaAlie/openits/issues).

## Vision

OpenITS remains a **self-hosted**, open-source platform for enterprise architecture documentation: C4 modeling, API catalogs, integration mapping, ADRs, and technology governance — without requiring a proprietary SaaS vendor.

## Planned (next 12 months)

### Q3 2026 — Quality & security hardening

| Item | Description |
|------|-------------|
| Static analysis | Add PHPStan/Larastan and run before releases |
| Backup maintainer | Document and onboard a second GitHub Admin maintainer (bus factor ≥ 2) |
| Security advisories | Enable GitHub private vulnerability reporting |
| Documentation | Keep API, CLI, and service reference docs current with code changes |

### Q4 2026 — Collaboration & governance

| Item | Description |
|------|-------------|
| C4 collaboration | Improve change-request workflows and notification hooks |
| ADR enhancements | Link ADRs more tightly to C4 elements and export |
| LDAP / SSO | Harden group-filter and provisioning documentation |
| Roadmap review | Revisit this document; publish next 12-month plan |

### Q1 2027 — Integration & export

| Item | Description |
|------|-------------|
| Import formats | Broader OpenAPI 3.1 / AsyncAPI coverage in C4 import pipeline |
| Export | Improved Structurizr DSL and landscape JSON interoperability |
| API surface | Expand documented REST endpoints where needed for integrations |
| Performance | Optimize large integration trees and C4 diagram load times |

### Q2 2027 — Platform maturity

| Item | Description |
|------|-------------|
| Data dictionary | Deeper canonical entity / field-mapping UX |
| Tech radar | Usage analytics and filtering improvements |
| Accessibility | Keyboard navigation and contrast improvements in the C4 editor |
| Release cadence | Regular semver releases with release notes |

## Under consideration (not committed)

These may be promoted to **Planned** based on community demand and maintainer capacity:

- Webhook notifications for C4 change requests
- Read-only public catalog mode (landscape export without login)
- Optional PostgreSQL support alongside MySQL/SQLite
- Mobile-friendly read-only views for ADRs and API docs

## Out of scope (not planned)

OpenITS will **not** pursue the following in this 12-month window:

| Not planned | Reason |
|-------------|--------|
| **Proprietary SaaS hosted edition** by the core project | OpenITS is self-hosted; hosting is left to users and integrators |
| **Replacing dedicated EA tools entirely** (e.g. full Sparx/LeanIX parity) | Focus is documentation and landscape modeling, not full ALM |
| **Built-in CI/CD pipeline execution** | Users run their own GitHub Actions / Jenkins; we document integration only |
| **Real-time multi-user C4 editing** (Google Docs style) | Collaboration uses comments and change requests, not live co-editing |
| **Native mobile apps** (iOS/Android) | Web UI only; responsive improvements may happen |
| **Blockchain / NFT governance** | Not aligned with enterprise architecture use cases |
| **Custom cryptography** | Use Laravel/PHP standard libraries only (see [SECURITY.md](SECURITY.md)) |
| **Mandatory telemetry or phone-home** | Self-hosted deployments must work offline/air-gapped |

## How to influence the roadmap

1. Open a [feature request](https://github.com/imRezaAlie/openits/issues/new/choose).
2. Explain the enterprise architecture problem you are solving.
3. Maintainers will label, discuss, and update this roadmap when scope is accepted.

See [GOVERNANCE.md](GOVERNANCE.md) for decision-making and [CONTRIBUTING.md](CONTRIBUTING.md) for how to contribute.
