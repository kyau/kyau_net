# 🤖 AGENTS.md

## Git

- All commits require GPG signature
- All commits must follow conventional commit syntax configured in this repo
- All commits must have detailed body messages
- Never commit/push directly to `main` or `develop`, create branches `<type>/kyau-(openssl rand -hex 3)-name-of-branch` where `<type>` is conventional commits type
- GitHub user `kyau` is authenticated via Yubikey to SSH/GPG for `git`
- GitHub users `kyau` and `kyaulabs-bot` are authenticated via `gh`
- All commits/reviews are to be done by `kyau`.
- All PRs must created/merged with the supplied template by `kyaulabs-bot`
- Once a PR is created the `Test Plan` section needs to be completed in full before `kyau` approves the review
- Approve reviews with `✔️ Approved by: @kyau`
- Watch all workflows, if they fail debug and fix automatically and then re-run
- Run all git commands automatically, approval granted

## Semantics

- Do not use sub-titles when just a title will do
- Do not insert random sub-titles unless asked to do so
- Simple is always better provided it gets the job done adaquetely
