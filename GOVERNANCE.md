# OpenITS Project Governance

This document describes how the OpenITS open-source project is governed: who makes decisions, what roles exist, and how contributors can participate.

## Project overview

OpenITS is a self-hosted enterprise architecture and integration documentation platform, maintained as an open-source project under the [Apache License 2.0](LICENSE).

Repository: [https://github.com/imRezaAlie/openits](https://github.com/imRezaAlie/openits)

## Governance model

OpenITS uses a **maintainer-led** governance model. Day-to-day technical decisions are made by project maintainers through review of issues and pull requests. Larger or controversial changes are discussed in GitHub Issues before implementation.

There is no separate steering committee or corporate board. The project lead holds final decision authority when consensus cannot be reached.

## Roles


| Role             | Responsibilities                                                                                |
| ---------------- | ----------------------------------------------------------------------------------------------- |
| **Project lead** | Overall direction, release approval, security contact, final tie-break on disputes              |
| **Maintainer**   | Review and merge pull requests, triage issues, guide technical direction                        |
| **Contributor**  | Submit issues, pull requests, documentation, and tests under [CONTRIBUTING.md](CONTRIBUTING.md) |
| **User**         | Deploy and use OpenITS; report bugs and request features via GitHub Issues                      |


### Current maintainers

OpenITS targets a **bus factor of 2 or more**: at least two people can manage issues, pull requests, and releases if one maintainer is unavailable.


| Name                                    | Role                      | GitHub                                                                                                                                     | Contact                                             |
| --------------------------------------- | ------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------ | --------------------------------------------------- |
| Reza Ali                                | Project lead & maintainer | [@imRezaAlie](https://github.com/imRezaAlie)                                                                                               | [rezaalie70@gmail.com](mailto:rezaalie70@gmail.com) |
| *(backup maintainer — update this row)* | Backup maintainer         | Grant **Admin** at [repository access settings](https://github.com/imRezaAlie/openits/settings/access), then add name and `@username` here | —                                                   |


> **Project lead:** Invite your backup maintainer on GitHub (**Settings → Collaborators → Admin**), then replace the placeholder row above with their name and GitHub handle. Either maintainer can triage issues, merge PRs, and publish releases.

## Continuity and succession

If the project lead is unable to continue support, the project MUST remain operational within **one week**:


| Capability        | How continuity is ensured                                                                                                               |
| ----------------- | --------------------------------------------------------------------------------------------------------------------------------------- |
| **Issues & PRs**  | Any GitHub **Admin** collaborator on [imRezaAlie/openits](https://github.com/imRezaAlie/openits) can manage issues and merges           |
| **Releases**      | Tags and GitHub Releases are created on the repository; Admins can publish new versions                                                 |
| **Source code**   | Public repository under [Apache 2.0](LICENSE); the community may fork and continue development                                          |
| **Secrets**       | Production secrets (`.env`) are **not** stored in the repository; deployments are operator-managed                                      |
| **Domain / demo** | [openits.ir](https://openits.ir) is operated separately; continuity of the **open-source project** does not depend on a single DNS name |


**Succession steps:**

1. Confirm the project lead is unable to continue (incapacitation, unavailability, or voluntary step-down).
2. The **backup maintainer** (GitHub Admin) assumes maintainer duties within 7 days.
3. If no backup maintainer is listed, active contributors may fork under Apache 2.0 and elect a new lead via public discussion in GitHub Issues.
4. Security reports continue via [SECURITY.md](SECURITY.md) (email or GitHub security advisories).

## Decision-making process

### Routine changes

1. Contributor opens a pull request or issue.
2. A maintainer reviews for quality, security, tests, and fit with project goals.
3. If approved, a maintainer merges the change to `main` (or `develop` for integration branches).

### Significant changes

For new features, breaking changes, or architectural shifts:

1. Open a GitHub Issue for discussion **before** large implementation work.
2. Maintainers and contributors discuss trade-offs in the issue thread.
3. A maintainer confirms whether the change is accepted.
4. Implementation proceeds via pull request with tests and documentation.

### Releases

- Releases are tagged on `main` using [Semantic Versioning](https://semver.org/) (e.g. `v1.1.0`).
- The project lead approves release tags and publishes release notes on GitHub.
- Security fixes may be released outside the normal schedule when needed (see [SECURITY.md](SECURITY.md)).

### Disputes

1. Discuss respectfully in the relevant issue or pull request.
2. If unresolved, the project lead makes a final decision.
3. All participants are expected to follow the [Code of Conduct](CODE_OF_CONDUCT.md).

## Becoming a maintainer

Maintainer status is granted by the project lead based on:

- Sustained, high-quality contributions over time
- Understanding of the codebase and project goals
- Constructive participation in reviews and community discussion
- Agreement to uphold the Code of Conduct and security practices

To express interest, open a GitHub Issue or contact the project lead directly.

## Related documents


| Document                                 | Purpose                                  |
| ---------------------------------------- | ---------------------------------------- |
| [CONTRIBUTING.md](CONTRIBUTING.md)       | How to contribute, coding standards, DCO |
| [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md) | Community behavior standards             |
| [ROADMAP.md](ROADMAP.md)                 | 12-month planned and out-of-scope work   |
| [UPGRADING.md](UPGRADING.md)             | Upgrade and version migration guide      |
| [SECURITY.md](SECURITY.md)               | Vulnerability reporting                  |
| [ASSURANCE_CASE.md](ASSURANCE_CASE.md)   | Security assurance case                  |
| [LICENSE](LICENSE)                       | Apache 2.0 license terms                 |


