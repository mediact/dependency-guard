# GrumPHP configuration

In your `grumphp.yml` file, add a new task, `dependency-guard`:

```yaml
parameters:
  tasks:
    dependency-guard:
      metadata:
        priority: 100
```

And add the following services:

```yaml
services:
  mediact.dependency_guard.exporter.factory:
    class: Mediact\DependencyGuard\Composer\Command\Exporter\ViolationExporterFactory

  mediact.dependency_guard.exporter:
    class: Mediact\DependencyGuard\Exporter\ViolationExporterInterface
    factory: 'mediact.dependency_guard.exporter.factory:create'
    arguments:
      - '@console.input'
      - '@console.output'

  mediact.dependency_guard.factory:
    class: Mediact\DependencyGuard\DependencyGuardFactory

  mediact.dependency_guard.composer.io:
    class: Composer\IO\BufferIO

  mediact.dependency_guard.composer.factory:
    class: Composer\Factory

  mediact.dependency_guard.composer:
    class: Composer\Composer
    factory: mediact.dependency_guard.composer.factory:createComposer
    arguments:
      - '@mediact.dependency_guard.composer.io'

  mediact.dependency_guard.grumphp.task:
    class: Mediact\DependencyGuard\GrumPHP\DependencyGuard
    arguments:
      - '@mediact.dependency_guard.composer'
      - '@mediact.dependency_guard.factory'
      - '@mediact.dependency_guard.exporter'
    tags:
      - name: grumphp.task
        config: dependency-guard
```

This will register the DependencyGuard task for GrumPHP.
