# Company domains and authenticated sessions

## Provisioning and access model

Akaunting companies remain the source of truth. A company may have several
verified, immediate subdomains under the configured canonical base host.
When the instance administrator explicitly specifies a trusted corporate
alias zone, a sibling hostname within that zone is also supported (for
example, accounting.example.org beside akaunting.example.org). The base
hostname must be an immediate child of the chosen zone. No arbitrary,
third-party or multi-level aliases are accepted.
Aliases share the same company theme. One active alias is primary.

1. An instance administrator enters the canonical hostname under Default
   instance login.
2. A company administrator registers a subdomain under Company domains.
   The record remains pending.
3. Provision a public DNS A/AAAA record for the reverse proxy, the displayed
   DNS TXT challenge and a valid HTTPS certificate on that hostname.
4. Select Verify DNS and HTTPS. The module validates the exact TXT value,
   a public destination IP, a trusted TLS certificate and an HTTP origin
   challenge served by this very Akaunting instance.
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

Install the Theme module in `modules/Theme` using the Akaunting module
system. Then register the **same directory** as a Composer path package from
the Akaunting application root:

    composer config repositories.librecode-theme path modules/Theme
    composer require 'librecodecoop/akaunting-theme:*@dev'
    php artisan package:discover --ansi

Composer creates a link under `vendor/librecodecoop/akaunting-theme` to the
existing module directory; it does not create a second module or modify the
Akaunting PHP source. Its normal Laravel discovery adds the
`Modules\Theme\Providers\Guest` provider to the **generated** package
manifest. Future `composer install` and `composer dump-autoload` retain
that provider because the package is declared in `composer.json` and
`composer.lock`. Do not edit `bootstrap/cache/packages.php` directly.

After installation, verify from the application root:

    php artisan tinker --execute="echo app()->providerIsLoaded(\Modules\Theme\Providers\Guest::class) ? 'registered' : 'missing';"

To unregister this early provider during a planned uninstall, remove the
Composer dependency before deleting the module directory:

    composer remove librecodecoop/akaunting-theme
    composer config --unset repositories.librecode-theme

**Application checkout upgrades:** a deployment that resets the Akaunting
checkout to a clean upstream tag with `git reset --hard` will also discard
local changes to its root Composer manifests. Such deployments must preserve
this **application dependency configuration** in their provisioned checkout,
or reinstall the Theme Composer dependency after the reset and before the
next Composer autoload rebuild. That is an application-dependency lifecycle
requirement; no Theme-specific shell hook belongs in a generic Docker
entrypoint. Composer-only updates that retain the dependency do not need any
manual provider repair.

This bootstrap enables public branding and the handoff endpoint. The
authenticated domain *enforcement* remains independently controlled by
AKAUNTING_THEME_DOMAIN_ROUTING_ENABLED (disabled by default).


Registering an alias never changes DNS or the proxy, and never proves that
the hostname serves this Akaunting instance. If the hostname currently
returns another service or HTTP 503, leave it pending until the proxy,
valid certificate and DNS TXT challenge are corrected. Enabling cross-host
session routing is a separate deployment decision.
