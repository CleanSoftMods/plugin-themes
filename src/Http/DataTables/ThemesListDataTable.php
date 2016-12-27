<?php namespace WebEd\Base\ThemesManagement\Http\DataTables;

use WebEd\Base\Core\Http\DataTables\AbstractDataTables;

class ThemesListDataTable extends AbstractDataTables
{
    protected $repository;

    public function __construct()
    {
        $this->repository = themes_management()->getAllThemesInformation()->values();

        parent::__construct();
    }

    /**
     * @return string
     */
    public function run()
    {
        $this->setAjaxUrl(route('admin::themes.index.post'), 'POST');

        $this
            ->addHeading('thumbnail', 'Thumbnail', '1%')
            ->addHeading('name', 'Name', '20%')
            ->addHeading('description', 'Description', '40%')
            ->addHeading('actions', 'Actions', '40%');

        $this->setColumns([
            ['data' => 'thumbnail', 'name' => 'thumbnail', 'searchable' => false, 'orderable' => false],
            ['data' => 'name', 'name' => 'name', 'searchable' => false, 'orderable' => false],
            ['data' => 'description', 'name' => 'description', 'searchable' => false, 'orderable' => false],
            ['data' => 'actions', 'name' => 'actions', 'searchable' => false, 'orderable' => false],
        ]);

        return $this->view();
    }

    /**
     * @return $this
     */
    protected function fetch()
    {
        $this->fetch = datatable()->of($this->repository)
            ->editColumn('description', function ($item) {
                return array_get($item, 'description') . '<br><br>'
                . 'Author: ' . array_get($item, 'author') . '<br>'
                . 'Version: <b>' . array_get($item, 'version', '...') . '</b>';
            })
            ->addColumn('thumbnail', function ($item) {
                $themeFolder = get_base_folder($item['file']);
                $themeThumbnail = $themeFolder . 'theme.jpg';
                if (!\File::exists($themeThumbnail)) {
                    $themeThumbnail = webed_themes_path('default-thumbnail.jpg');
                }
                $imageData = base64_encode(\File::get($themeThumbnail));
                $src = 'data: ' . mime_content_type($themeThumbnail) . ';base64,' . $imageData;
                return '<img src="' . $src . '" alt="' . array_get($item, 'alias') . '" width="240" height="180" class="theme-thumbnail">';
            })
            ->addColumn('actions', function ($item) {
                $activeBtn = (!array_get($item, 'enabled')) ? form()->button('Active', [
                    'title' => 'Active this theme',
                    'data-ajax' => route('admin::themes.change-status.post', [
                        'module' => array_get($item, 'alias'),
                        'status' => 1,
                    ]),
                    'data-method' => 'POST',
                    'data-toggle' => 'confirmation',
                    'class' => 'btn btn-outline blue btn-sm ajax-link',
                ]) : '';
                $disableBtn = (array_get($item, 'enabled')) ? form()->button('Disable', [
                    'title' => 'Disable this theme',
                    'data-ajax' => route('admin::themes.change-status.post', [
                        'module' => array_get($item, 'alias'),
                        'status' => 0,
                    ]),
                    'data-method' => 'POST',
                    'data-toggle' => 'confirmation',
                    'class' => 'btn btn-outline yellow-lemon btn-sm ajax-link',
                ]) : '';

                $installBtn = (array_get($item, 'enabled') && !array_get($item, 'installed')) ? form()->button('Install', [
                    'title' => 'Install this theme\'s dependencies',
                    'data-ajax' => route('admin::themes.install.post', [
                        'module' => array_get($item, 'alias'),
                    ]),
                    'data-method' => 'POST',
                    'data-toggle' => 'confirmation',
                    'class' => 'btn btn-outline blue btn-sm ajax-link',
                ]) : '';

                $uninstallBtn = (array_get($item, 'enabled') && array_get($item, 'installed')) ? form()->button('Uninstall', [
                    'title' => 'Uninstall this theme\'s dependencies',
                    'data-ajax' => route('admin::themes.uninstall.post', [
                        'module' => array_get($item, 'alias'),
                    ]),
                    'data-method' => 'POST',
                    'data-toggle' => 'confirmation',
                    'class' => 'btn btn-outline red-sunglo btn-sm ajax-link',
                ]) : '';

                return $activeBtn . $disableBtn . $installBtn . $uninstallBtn;
            });

        return $this;
    }
}
