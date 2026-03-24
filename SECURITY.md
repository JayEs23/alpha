# Security Policy

## Supported Versions

Security fixes are applied to the active `master` branch.

## Reporting a Vulnerability

- Do not open public issues for sensitive vulnerabilities.
- Report security concerns privately to the project maintainers.
- Include:
  - affected area and impact
  - reproduction steps
  - suggested remediation (if available)

## Security Baseline

- Keep secrets in `.env` only, never in version control.
- Use explicit CORS origin allowlists through `CORS_ALLOWED_ORIGINS`.
- Restrict privileged access to Filament admin routes by role.
- Rotate credentials and keys when cloning environments.
