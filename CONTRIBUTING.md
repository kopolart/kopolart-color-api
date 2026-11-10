# Contributing

Thanks for helping improve the Kopolart Color API documentation.

This repository contains **documentation only**. The API implementation and
database are private and not part of this repository.

## What is welcome

- Corrections to endpoint behavior, field descriptions or examples.
- New client examples (Go, Rust, C#, Kotlin, Dart, ...).
- Typo, formatting and accessibility fixes.
- Clarifications for edge cases (achromatic colors, 3-digit HEX, rate limits).

## Before you open a pull request

1. **Test examples against the live API** at `https://kopolart.ir`.
2. **Validate the OpenAPI spec**:

   ```bash
   npx --yes @redocly/cli@1 lint openapi.yaml
   ```

3. **Preview the interactive docs**:

   ```bash
   python -m http.server 8080
   # open http://localhost:8080/docs/
   ```

4. Keep Markdown focused: one sentence per idea, tables for field references,
   and fenced code blocks with a language tag.
5. Do not include API keys, credentials, personal data or scraped datasets.

## Style guide

- Write in clear, neutral English; Persian text is welcome in examples.
- Use `HEX` in prose and `hex` for the path parameter.
- Prefer absolute URLs in examples (`https://kopolart.ir/...`).
- Keep JSON examples valid and consistent with the OpenAPI schemas.
- Do not document behavior you have not observed or verified.

## Reporting API problems

Open an issue using the bug report template. Include:

- the exact request URL,
- the HTTP status code,
- the response body (if any),
- the client or tool you used.

For security vulnerabilities, follow [SECURITY.md](./SECURITY.md) instead of
opening a public issue.

## License

By contributing, you agree that your contributions are licensed under the
[MIT License](./LICENSE).
