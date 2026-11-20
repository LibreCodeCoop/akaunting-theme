# Company domains and authenticated sessions

## Provisioning and access model

Akaunting companies remain the source of truth. A company may have several
verified, immediate subdomains under the configured canonical base host.
Aliases share the same company theme. One active alias is primary.

1. An instance administrator enters the canonical hostname under Default
   instance login.
2. A company administrator registers a subdomain under Company domains.
   The record remains pending.
3. Provision a public DNS A/AAAA record for the reverse proxy, the displayed
   DNS TXT challenge and a valid HTTPS certificate on that hostname.
4. Select Verify DNS and HTTPS. The module validates the exact TXT value,
   a public destination IP and a trusted TLS certificate.
5. Activate only after verification. Duplicate hostnames, arbitrary external
   domains, private destinations, wildcards and nested subdomains are rejected.

Do not confuse DNS validation with actual TLS and proxy readiness. A pending
domain is not permitted to serve a company-branded login.

## Session handoff after activation

Browser routing is disabled by default. After real-domain E2E validation, an
operator can set the web-runtime variable
AKAUNTING_THEME_DOMAIN_ROUTING_ENABLED=true.

When enabled, logging in from A with access only to B selects B and its
canonical hostname. If the user can access both, the chosen A domain is
preferred. A company switch uses the destination company's verified
primary hostname, or the canonical instance host as a fallback.

The module never shares the session cookie across subdomains. Instead it
issues a random, one-use transfer code bound to the target company and host
and valid for at most 45 seconds. Only its hash is persisted. The source
session is invalidated. Redemption locks and consumes the code, revalidates
the user's enabled company membership and regenerates the destination
session. Unauthorized requests and attempts to mutate another company from
the wrong domain are rejected. Core Akaunting authorization remains active.

The code appears briefly in a private, no-store HTTPS redirect URL.
Do not enable this mechanism without audited HTTPS, trusted reverse proxies,
test cases for replay/expiry/cross-host access and verified cookies.
Use a real browser to test a complete A-to-B login and B-to-A switch.
API/MCP authorization stays under the Core's independent API middleware.

## Operational gate and fallback

Until DNS and TLS exist, the domain may be registered as pending but cannot
be activated. The module's routing feature flag stays off; the canonical
Akaunting installation and all existing sessions remain unchanged.
Schedule the maintenance command php artisan theme:prune-handoffs in the operator's normal cron after enabling the feature; it removes expired one-time transfer records in bounded batches. Tests should use disposable, synthetic companies and sessions. Rollback is
the previous module version and removal of the activation flag.

This is company-aware routing and public login branding, not a new identity
provider or a separate per-company database.

## Early public-login provider (deployment integration)

Akaunting loads company modules only after identifying a company. Its public
login has no such context. A separate lightweight Laravel provider in the
Theme module is therefore required for anonymous login and the public handoff
endpoint. It is declared in the Theme package's composer.json for installations
using normal Composer package discovery.

For the existing Akaunting module-directory installation, **without editing
the Akaunting PHP source or root composer manifests**, register the Theme's
public provider in Laravel's generated package manifest:

    php modules/Theme/scripts/register_guest_provider.php /var/www/html install
    php modules/Theme/scripts/register_guest_provider.php /var/www/html verify

The script is idempotent, touches only bootstrap/cache/packages.php and
preserves existing packages. If Composer package discovery regenerates this
cache, re-run the install command as a deployment step. To reverse it:

    php modules/Theme/scripts/register_guest_provider.php /var/www/html remove

An installation using normal Composer path-package discovery does not need
the generated-manifest fallback because the package advertises the provider.

This bootstrap enables public branding and the handoff endpoint. The
authenticated domain *enforcement* remains independently controlled by
AKAUNTING_THEME_DOMAIN_ROUTING_ENABLED (disabled by default).
