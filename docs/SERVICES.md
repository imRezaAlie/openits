# Services Reference

OpenITS business logic lives in `app/Services/`. These classes are the primary programmatic interface for extending or integrating with the application. Controllers and jobs delegate to these services.

Namespace: `App\Services`

---

## SettingsService

Cached application settings with database and environment fallbacks.

| Method | Returns | Description |
|--------|---------|-------------|
| `get(string $key, mixed $default = null)` | `mixed` | Retrieve a setting (cached) |
| `set(string $key, mixed $value, string $type = 'string')` | `Setting` | Persist a setting and refresh cache |
| `isGoogleLoginEnabled()` | `bool` | Whether Google OAuth login is enabled |
| `setGoogleLoginEnabled(bool $enabled)` | `Setting` | Toggle Google login |
| `googleCredentialsConfigured()` | `bool` | OAuth client ID, secret, and redirect are set |
| `isLdapLoginEnabled()` | `bool` | Whether LDAP login is enabled |
| `setLdapLoginEnabled(bool $enabled)` | `Setting` | Toggle LDAP login |
| `getLdapServer()` | `?string` | LDAP hostname |
| `getLdapPort()` | `int` | LDAP port |
| `getLdapBaseDn()` | `?string` | LDAP base DN |
| `getLdapDomain()` | `?string` | LDAP domain |
| `setLdapSettings(array $settings)` | `void` | Persist LDAP connection settings from admin panel |
| `ldapCredentialsConfigured()` | `bool` | Minimum LDAP settings are present |
| `getLdapConfig()` | `array` | Merged LDAP configuration |
| `getAvailableLdapDomains()` | `string[]` | Allowed sign-in domains |
| `forgetCache(string $key)` | `void` | Clear cache for one key |
| `flushCache()` | `void` | Clear all settings cache |

---

## LoginThrottleService

Brute-force protection for authentication endpoints.

| Method | Returns | Description |
|--------|---------|-------------|
| `maxAttempts()` | `int` | Per-credential attempt limit |
| `decaySeconds()` | `int` | Lockout duration in seconds |
| `ipMaxAttempts()` | `int` | Per-IP attempt limit |
| `tooManyAttempts(Request $request, string $scope, ?string $identifier)` | `bool` | Check if credential or IP is locked out |
| `tooManyIpAttempts(Request $request, string $scope)` | `bool` | Check IP-only lockout |
| `hitFailure(Request $request, string $scope, ?string $identifier)` | `void` | Record a failed attempt |
| `hitIpFailure(Request $request, string $scope)` | `void` | Record IP-level failure |
| `clearCredential(Request $request, string $scope, string $identifier)` | `void` | Clear counter on successful login |
| `availableIn(Request $request, string $scope, ?string $identifier)` | `int` | Seconds until retry allowed |
| `lockoutMessage(Request $request, string $scope, ?string $identifier)` | `string` | Localized throttle message |

**Scopes:** `ldap`, `google`, and standard Laravel auth scopes.

---

## LdapService

LDAP directory connectivity and user synchronization.

| Method | Returns | Description |
|--------|---------|-------------|
| `testConnection(?array $overrides = null)` | `array` | Test server connectivity; `['success' => bool, 'message' => string]` |
| `authenticate(string $username, string $password, ?string $domain)` | `array` | Bind and fetch user attributes |
| `fetchAllUsers()` | `array` | List directory users for sync |
| `syncAllUsers()` | `int` | Sync all users; returns count |
| `logAttempt(...)` | `void` | Write to `ldap_logs` audit table |
| `buildBindIdentifier(string $username, ?string $domain)` | `string` | Construct bind DN/UPN |
| `attributeList()` | `array` | LDAP attributes to fetch |
| `isActiveDirectory()` | `bool` | Whether server type is AD |
| `connect()` | `\LDAP\Connection` | Open LDAP connection |
| `normalizeEntry(array $entry, string $username, ?string $domain)` | `array` | Normalize LDAP entry to user array |

---

## LdapAuthService

Map LDAP users to local `User` records.

| Method | Returns | Description |
|--------|---------|-------------|
| `findOrCreateUser(array $ldapUser)` | `User` | Find existing or create user per provisioning rules |
| `syncUserFromLdap(array $ldapUser, ?User $user)` | `User` | Update local user from LDAP attributes |
| `linkLdapAccount(User $user, array $ldapUser)` | `User` | Link LDAP identity to existing user |
| `registerUser(array $ldapUser)` | `User` | Create new user from LDAP entry |

---

## GoogleAuthService

Map Google OAuth users to local `User` records.

| Method | Returns | Description |
|--------|---------|-------------|
| `findOrCreateUser(SocialiteUser $googleUser)` | `User` | Find or create per provisioning rules |
| `linkGoogleAccount(User $user, SocialiteUser $googleUser)` | `User` | Link Google account to existing user |
| `registerUser(SocialiteUser $googleUser)` | `User` | Create new user from Google profile |

---

## C4DiagramService

Build C4 diagram graph data for the D3 editor.

| Method | Returns | Description |
|--------|---------|-------------|
| `buildContextDiagram(System $system)` | `array` | Context-level nodes and edges |
| `buildContainerDiagram(System $system)` | `array` | Container-level diagram |
| `buildComponentDiagram(C4Container $container)` | `array` | Component-level diagram |
| `search(System $system, string $query)` | `Collection` | Search elements across C4 levels |

---

## C4ExportService

Export C4 models to external formats.

| Method | Returns | Description |
|--------|---------|-------------|
| `toStructurizrDsl(System $system)` | `string` | Structurizr DSL text |
| `toDrawIoXml(System $system)` | `string` | Draw.io XML |
| `toPlantUml(System $system)` | `string` | PlantUML diagram source |
| `toJson(System $system)` | `array` | JSON representation |

---

## C4ImportService

Orchestrate C4 model imports.

| Method | Returns | Description |
|--------|---------|-------------|
| `createImport(System $system, string $format, ...)` | `C4Import` | Create import job record |
| `process(C4Import $import)` | `void` | Process import (OpenAPI, AsyncAPI, Structurizr, JSON) |

**Import implementations** in `App\Services\C4Import\`:

| Class | Format |
|-------|--------|
| `C4OpenApiImportService` | OpenAPI 3.x |
| `C4AsyncApiImportService` | AsyncAPI |
| `C4StructurizrImportService` | Structurizr DSL |
| `C4JsonBackupImportService` | OpenITS JSON backup |

---

## C4SyncService

Synchronize C4 models from API documentation.

| Method | Returns | Description |
|--------|---------|-------------|
| `enableC4ForSystem(System $system)` | `C4Context` | Initialize C4 for a system |
| `syncFromApis(System $system)` | `void` | Sync containers/components from APIs |
| `syncTechnologies(System $system)` | `void` | Sync technology metadata |

---

## C4VersionService

C4 model versioning and snapshots.

| Method | Returns | Description |
|--------|---------|-------------|
| `snapshot(System $system, string $commitMessage, string $branch)` | `C4ModelVersion` | Create version snapshot |
| `rollback(System $system, C4ModelVersion $version)` | `void` | Restore a previous version |
| `diff(C4ModelVersion $from, C4ModelVersion $to)` | `array` | Diff two versions |

---

## C4CommentService

C4 collaboration comments and threads.

| Method | Returns | Description |
|--------|---------|-------------|
| `forElement(string $type, string $id)` | `Collection` | Comments for a diagram element |
| `store(...)` | `C4Comment` | Create a comment |
| `resolve(C4Comment $comment, bool $resolved)` | `C4Comment` | Mark comment resolved/unresolved |
| `resolveCommentable(string $type, string $id)` | `Model` | Resolve polymorphic comment target |
| `toThreadArray(C4Comment $comment)` | `array` | Serialize comment for API response |

---

## C4ContextElementService

C4 element UUID and relationship ID resolution.

| Method | Returns | Description |
|--------|---------|-------------|
| `ensureElementUuids(C4Context $context)` | `C4Context` | Assign UUIDs to context elements |
| `resolveRelationshipId(System $system, string $rawId, string $field)` | `string` | Resolve relationship endpoint ID |
| `contextRelationshipIds(System $system, ?C4Context $context)` | `array` | List valid relationship IDs |

---

## C4RelationshipValidator

| Method | Returns | Description |
|--------|---------|-------------|
| `validateNoCycle(string $sourceId, string $targetId, ?string $excludeId)` | `void` | Throws if relationship would create a cycle |

---

## OpenApiSpecBuilder

| Method | Returns | Description |
|--------|---------|-------------|
| `build(Api $api, ?ApiVersion $version)` | `array` | Build OpenAPI 3 spec array |
| `buildAndPersist(Api $api, ?ApiVersion $version)` | `array` | Build and save to API record |
| `buildForImport(...)` | `array` | Build spec for C4 import pipeline |

---

## OpenApiImporter

| Method | Returns | Description |
|--------|---------|-------------|
| `import(UploadedFile\|string $source, ?string $baseUrl)` | `array` | Parse OpenAPI file and return API structure |

---

## ProtocolSpecBuilder

| Method | Returns | Description |
|--------|---------|-------------|
| `build(Api $api, ?ApiVersion $version)` | `array` | Build protocol-specific spec (REST, GraphQL, gRPC, etc.) |

---

## SoapSpecBuilder

| Method | Returns | Description |
|--------|---------|-------------|
| `build(Api $api, ?ApiVersion $version)` | `array` | Build SOAP/WSDL spec |
| `buildFromFields(Api $api, ?ApiVersion $version)` | `array` | Build from stored SOAP fields |

---

## WsdlImporter

| Method | Returns | Description |
|--------|---------|-------------|
| `import(UploadedFile\|string $source, ?string $wsdlUrl)` | `array` | Parse WSDL and return API structure |

---

## IntegrationCatalogService

| Method | Returns | Description |
|--------|---------|-------------|
| `query(?int $domainId, ?int $vendorId, ?string $apiType)` | `Collection` | Filter integration links |
| `stats(Collection $links)` | `array` | Aggregate statistics |
| `toCsv(Collection $links)` | `string` | Export links as CSV |
| `buildLandscapeExport()` | `array` | Full EA landscape JSON export |

---

## MappingCatalogService

Data dictionary and field mapping catalog.

| Method | Returns | Description |
|--------|---------|-------------|
| `query(?int $systemId, ?int $entityId)` | `Collection` | Query mappings |
| `stats()` | `array` | Catalog statistics |
| `buildExport()` | `array` | Export mapping catalog |

---

## SchemaImportService

| Method | Returns | Description |
|--------|---------|-------------|
| `importFromApi(Api $api, ?ApiVersion $version)` | `PlatformSchema` | Import schema from API spec |
| `importFromSystem(System $system)` | `array` | Import schemas for all system APIs |

---

## TechRadarService

| Method | Returns | Description |
|--------|---------|-------------|
| `buildChartData()` | `array` | Radar chart blip data |
| `usageReport()` | `Collection` | Technology usage across systems |

---

## TpsService

Transactions-per-second metrics for APIs.

| Method | Returns | Description |
|--------|---------|-------------|
| `record(Api $api, float $tpsValue, ?string $notes, ?DateTimeInterface $recordedAt)` | `TpsMetric` | Record a TPS measurement |
| `getHistory(Api $api, int $limit)` | `Collection` | Historical TPS records |
| `getChartData(Api $api, int $limit)` | `array` | Chart-ready TPS data |
| `getCurrentTps(Api $api)` | `?float` | Latest TPS value |

---

## SystemMarkdownGenerator

| Method | Returns | Description |
|--------|---------|-------------|
| `availableTypes()` | `array` | Document types that can be generated |
| `generate(System $system, string $type)` | `string` | Generate markdown for a system |
| `persist(System $system, string $type, ?string $version)` | `SystemDocument` | Generate and save document |

---

## Extending services

To add new functionality:

1. Create a service class in `app/Services/`.
2. Register dependencies via constructor injection (Laravel auto-resolves).
3. Call from controllers, jobs, or Artisan commands — avoid duplicating logic in controllers.
4. Add unit or feature tests under `tests/`.
5. Document public methods in this file and PHPDoc blocks.

See [CONTRIBUTING.md](../CONTRIBUTING.md) for coding standards.
