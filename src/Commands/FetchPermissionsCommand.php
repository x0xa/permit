<?php

namespace Nahid\Permit\Commands;

use Illuminate\Console\Command;
use Nahid\Permit\Permissions\PermissionRepository;
use Nahid\Permit\Users\UserRepository;

class FetchPermissionsCommand extends Command
{

    /**
     * @var PermissionRepository
     */
    protected $permission;

    /**
     * @var UserRepository
     */
    protected $user;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permit:fetch {type : two types 1. user 2. role} {needle : desire permission entity}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get permissions list for user and role';


    /**
     * FetchPermissionsCommand constructor.
     *
     * @param PermissionRepository $permissionRepository
     * @param UserRepository       $userRepository
     */
    public function __construct(PermissionRepository $permissionRepository, UserRepository $userRepository)
    {
        parent::__construct();

        $this->user = $userRepository;
        $this->permission = $permissionRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $type = $this->argument('type');

        if ($type == 'user') {
            $this->getUserPermissions();
        }

        if ($type == 'role') {
            $this->getRolePermissions();
        }
    }

    /**
     * get user all permission
     */
    public function getUserPermissions()
    {
        $headers = ['Ability', 'Permission'];

        $user = $this->user->find($this->argument('needle'));
        if ($user) {
            $permissions = $user->abilities;

            if (!is_array($permissions)) {
                $permissions = [];
            }

            foreach ($permissions as $module=>$permission) {
                $this->warn("\n" . strtoupper($module));
                $data = [];
                foreach ($permission as $ability=>$perm) {
                    $vals = [$ability];
                    if (is_bool($perm)) {
                        if ($perm) {
                            $vals[] = 'true';
                        } else {
                            $vals[] = 'false';
                        }
                    }
                    if (is_string($perm)) {
                        $vals[] = $perm;
                    }
                    $data[] = $vals;
                }
                $this->table($headers, $data);
            }

        } else {
            $this->error("No user found!");
        }
    }

    /**
     * get role all permission
     */
    public function getRolePermissions()
    {
        $headers = ['Ability', 'Permission'];

        $role_name = $this->argument('needle');

        $role = $this->permission->findBy('role_name', $role_name);
        if ($role) {
            $permissions = json_to_array($role->permission);

            if (!is_array($permissions)) {
                $permissions = [];
            }

            foreach ($permissions as $module=>$permission) {
                $this->warn("\n" . strtoupper($module));
                $data = [];

                foreach ($permission as $ability=>$perm) {
                    $vals = [$module, $ability];
                    if (is_bool($perm)) {
                        if ($perm) {
                            $vals[] = 'true';
                        } else {
                            $vals[] = 'false';
                        }
                    }
                    if (is_string($perm)) {
                        $vals[] = $perm;
                    }
                    $data[] = $vals;
                }
                $this->table($headers, $data);
            }

        } else {
            $this->error("No role found!");
        }
    }
}
