<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class GenerateRolePermissionPolicies extends Command
{
    protected $signature = 'make:role-permission-policies';
    protected $description = 'Auto-generate RolePolicy and PermissionPolicy and register them';

    public function handle(): int
    {
        $fs = new Filesystem;

        // 1. Create Policy Directory if needed
        $fs->ensureDirectoryExists(app_path('Policies'));

        // 2. Create RolePolicy
        $fs->put(app_path('Policies/RolePolicy.php'), $this->rolePolicyStub());

        // 3. Create PermissionPolicy
        $fs->put(app_path('Policies/PermissionPolicy.php'), $this->permissionPolicyStub());

        // 4. Insert Gate::policy into AppServiceProvider if not present
        $provider = app_path('Providers/AppServiceProvider.php');
        $content = $fs->get($provider);

        $policyLines = <<<EOF
        use App\\Policies\\RolePolicy;
        use App\\Policies\\PermissionPolicy;
        use Spatie\\Permission\\Models\\Role;
        use Spatie\\Permission\\Models\\Permission;

        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);
        EOF;

        // if not already inserted
        if (!str_contains($content, 'RolePolicy')) {
            $content = preg_replace(
                '/public function boot\([^\)]*\): void\s*\{/',
                "public function boot(): void\n    {\n        {$policyLines}\n",
                $content
            );
            $fs->put($provider, $content);
        }

        $this->info('✔ RolePolicy & PermissionPolicy generated and registered successfully.');
        return Command::SUCCESS;
    }

    private function rolePolicyStub(): string
    {
        return <<<PHP
        <?php

        namespace App\\Policies;

        use App\\Models\\User;
        use Spatie\\Permission\\Models\\Role;

        class RolePolicy
        {
            public function viewAny(User \$user): bool
            {
                return \$user->hasRole('super admin');
            }

            public function view(User \$user, Role \$role): bool
            {
                return \$user->hasRole('super admin');
            }

            public function create(User \$user): bool
            {
                return \$user->hasRole('super admin');
            }

            public function update(User \$user, Role \$role): bool
            {
                return \$user->hasRole('super admin');
            }

            public function delete(User \$user, Role \$role): bool
            {
                return \$user->hasRole('super admin');
            }
        }
        PHP;
    }

    private function permissionPolicyStub(): string
    {
        return <<<PHP
        <?php

        namespace App\\Policies;

        use App\\Models\\User;
        use Spatie\\Permission\\Models\\Permission;

        class PermissionPolicy
        {
            public function viewAny(User \$user): bool
            {
                return \$user->hasRole('super admin');
            }

            public function view(User \$user, Permission \$permission): bool
            {
                return \$user->hasRole('super admin');
            }

            public function create(User \$user): bool
            {
                return \$user->hasRole('super admin');
            }

            public function update(User \$user, Permission \$permission): bool
            {
                return \$user->hasRole('super admin');
            }

            public function delete(User \$user, Permission \$permission): bool
            {
                return \$user->hasRole('super admin');
            }
        }
        PHP;
    }
}
