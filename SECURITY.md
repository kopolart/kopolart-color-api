# Security policy

## Supported versions

This repository documents the hosted API at `https://kopolart.ir`. There are no
versioned releases of the service; the current production behavior is the
supported version.

## Reporting a vulnerability

Please **do not open a public issue** for security problems.

Use GitHub's private reporting flow:

1. Open the **Security** tab of this repository.
2. Choose **Advisories → Report a vulnerability**.
3. Describe the issue, the affected endpoint, and reproduction steps.

Include, when possible:

- the request that triggers the issue,
- the observed response,
- the potential impact,
- any suggested mitigation.

You will receive an acknowledgement as soon as the report is reviewed. Please
allow reasonable time for a fix before public disclosure.

## Scope

In scope:

- authentication or authorization bypass,
- injection or request smuggling,
- exposure of server data or credentials,
- rate-limit bypass with meaningful impact,
- denial of service caused by a single request.

Out of scope:

- missing CORS headers (documented behavior, not a vulnerability),
- rate limiting itself,
- automated scanner output without a reproducible impact,
- issues in third-party clients or examples.

## Handling credentials

This repository must never contain secrets. If you spot one, report it privately
using the flow above rather than opening an issue.
