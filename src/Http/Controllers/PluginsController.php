<?php namespace WebEd\Base\ModulesManagement\Http\Controllers;

use WebEd\Base\Core\Http\Controllers\BaseAdminController;
use WebEd\Base\Core\Support\DataTable\DataTables;
use WebEd\Base\ModulesManagement\Http\DataTables\PluginsListDataTable;
use WebEd\Base\ModulesManagement\Repositories\Contracts\PluginsRepositoryContract;
use WebEd\Base\ModulesManagement\Repositories\PluginsRepository;

class PluginsController extends BaseAdminController
{
    protected $module = 'webed-modules-management';

    protected $dashboardMenuId = 'webed-plugins';

    /**
     * @param PluginsRepository $repository
     */
    public function __construct(PluginsRepositoryContract $repository)
    {
        parent::__construct();

        $this->repository = $repository;
    }

    /**
     * Get index page
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getIndex(PluginsListDataTable $pluginsListDataTable)
    {
        $this->breadcrumbs->addLink('Plugins');

        $this->setPageTitle('Plugins');

        $this->getDashboardMenu($this->dashboardMenuId);

        $this->dis['dataTable'] = $pluginsListDataTable->run();

        return do_filter('webed-modules-plugin.index.get', $this)->viewAdmin('plugins-list');
    }

    /**
     * Set data for DataTable plugin
     * @param DataTables $dataTable
     * @return \Illuminate\Http\JsonResponse
     */
    public function postListing(PluginsListDataTable $pluginsListDataTable)
    {
        return do_filter('datatables.webed-modules-plugin.index.post', $pluginsListDataTable, $this);
    }

    public function postChangeStatus($module, $status)
    {
        switch ((bool)$status) {
            case true:
                return modules_management()->enableModule($module)->refreshComposerAutoload();
                break;
            default:
                return modules_management()->disableModule($module)->refreshComposerAutoload();
                break;
        }
    }

    public function postInstall($alias)
    {
        $module = get_module_information($alias);

        if(!$module) {
            return response_with_messages('Plugin not exists', true, 500);
        }

        \Artisan::call('module:install', [
            'alias' => $alias
        ]);

        return response_with_messages('Installed plugin dependencies');
    }

    public function postUninstall($alias)
    {
        $module = get_module_information($alias);

        if(!$module) {
            return response_with_messages('Plugin not exists', true, 500);
        }

        \Artisan::call('module:uninstall', [
            'alias' => $alias
        ]);

        return response_with_messages('Uninstalled plugin dependencies');
    }
}
