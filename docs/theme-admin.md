# Theme administration

The independent Theme module customizes presentation only. It does not
create or delete Akaunting companies, change financial data or remove
Akaunting's original attribution.

## Company themes

In Settings > Themes by company, users see their enabled Akaunting companies.
A sole company opens directly. The editor always displays the company name
and ID. Access is protected by the Core company-settings authorization.

The public login background appears as a **configurable-opacity decorative layer**
behind the UI, using contain sizing rather than a full-strength crop, to
maintain contrast when the image contains large text or a logo. On small
screens, opacity is reduced further.

Each image control has a saved preview, a replacement input and an unlink
control. An empty input preserves the saved file. Upload wins if a new file
and unlink are submitted together. Unlink clears the active reference; old
public files may remain until separate storage cleanup. Do not upload
non-public or sensitive images.

The editor groups identity, appearance, public-login resources and support.
Company login image and carousel edits are unlocked only after the company
has a verified active domain; existing previews remain visible before that.

## Instance-wide login

Default instance login can be viewed by users with permission to read
company settings. Editing it is restricted to users allowed by Akaunting to
create, update and delete companies. It is not a company theme.

The admin configures the main hostname (without scheme or path), global
logo/favicon/background, carousel visibility and up to six slides.
Each slide has a title, a caption and an image. Without configured slides,
the public carousel stays hidden rather than unexpectedly showing the Core default slides. Company-specific slides override the
global set; otherwise they inherit it. Company logo/background/favicon
also take precedence where configured.

The canonical host can be set only from the actual main host. Its change
is blocked when active company domains exist.

This module does not create new account credentials or company membership.

## Release checks

Compile Blade and then lint the generated PHP. A successful Blade cache
alone does not prove the page has valid PHP. Check saved slide thumbnails, add/remove/undo actions, and file previews and
contrast in both appearance modes; verify real domain behavior separately.

## Public login provider

Akaunting's normal module provider is loaded in authenticated company routes.
A separate early provider (Modules\Theme\Providers\Guest) must be
registered through Laravel package discovery for public login and browser
company-domain routing. This provider does not register company admin routes.
Its PHP source lives in the independent module.

For installations using a module directory instead of Composer's usual package
require flow, use the module's provider registration script described in
company-domains.md. Reapply after framework/composer package discovery runs.

The global login can append a configurable text/HTTPS credit below the
original Akaunting attribution. The original attribution must remain intact.
The login background has an administrator-adjustable opacity (0–100%);
artwork should keep its financial motif on the carousel side and preserve
legibility around the credential form.
