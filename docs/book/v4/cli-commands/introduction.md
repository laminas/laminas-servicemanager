# Introduction

INFO:
Starting in 4.0.0, `laminas-servicemanager` moved the CLI tooling to [`laminas-cli`](https://docs.laminas.dev/laminas-cli/) and provides several commands to be executed.

> MISSING: **Installation Requirements**
> 
> To run the console tools with `laminas-servicemanager`, the `laminas/laminas-cli` component needs to be added to the project dependencies.
>
> ```bash
> $ composer require laminas/laminas-cli
> ```
>
> _In case laminas-cli is only required to consume these console tools, you might consider using the `--dev` flag._

## Available Commands

- [Generate Dependencies for Config Factory](generate-dependencies-for-config-factory.md)
- [Generate Factory for Class](generate-factory-for-class.md)
- [Generate Ahead of Time Factories](generate-ahead-of-time-factories.md)

## Learn More

- [laminas-cli: Writing Custom Commands for laminas-mvc and Mezzio based Applications](https://docs.laminas.dev/laminas-cli/)