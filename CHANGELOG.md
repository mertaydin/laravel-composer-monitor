# Changelog

## v0.1.25 - 2026-11-19

- Restore the centered loading dialog and animated progress bar while the dependency check runs.
- Keep the existing POST/redirect flow and prevent duplicate submissions.


## v0.1.24 - 2026-11-19

- Patch release; retain the refreshed dependency dashboard preview and current Composer actions.


## v0.1.23 - 2026-11-18

- Refresh the README dashboard preview with the latest dependency inventory, update status, security advisory, and package health layout.
- Publish the refreshed documentation preview.


## v0.1.22 - 2026-11-18

- Publish a patch release; no functional code changes.

## v0.1.21 - 2026-11-18

- Clarify the loading message to state that the Composer update command is running in all supported languages.
- Replace the full-screen dependency-check overlay with a slim animated progress bar fixed to the top of the dashboard while preserving the existing POST/redirect flow.

## v0.1.20 - 2026-11-17

- Added per-package Composer Show and Composer Why actions with modal output.
- Added Composer Audit and Composer Suggests actions with structured dashboard dialogs.
- Added translations and responsive modal styling for all supported dashboard languages.

## v0.1.19 - 2026-11-17

- Show a centered, full-screen loading indicator while the dependency check is running.
- Disable the check button during submission to prevent duplicate requests.
- Restore the dashboard controls when the page is shown again, including browser back-forward cache restores.
- Keep loading styles in the external stylesheet for CSP compatibility.

