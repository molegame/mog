<?php

namespace App\Widgets;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Widgets\Widget;
use Illuminate\Contracts\Support\Renderable;

class ZoneSelect extends Widget implements Renderable
{
    /**
     * @var string
     */
    protected $className;

    /**
     * Alert constructor.
     *
     */
    public function __construct()
    {
    }

    /**
     * Get select class name.
     *
     * @return string
     */
    protected function getElementClassName()
    {
        if (!$this->className) {
            $this->className = uniqid().'-select';
        }

        return $this->className;
    }

    /**
     * Set up script for export button.
     */
    protected function setUpScripts()
    {
        $url = '/api/user';
        $language = config('app.locale');
        $placeholder = trans('game.select_zone');
        $zonePrefix = trans('game.name');
        $zonePost = trans('game.zone');

        $script = <<<SCRIPT
$('.{$this->getElementClassName()}').select2({
    ajax: {
        url: '$url',
        dataType: 'json',
        data: function (params) {
          return {
            q: params.term, // search term
            page: params.page
          };
        },
        processResults: function (data, params) {
          // parse the results into the format expected by Select2
          // since we are using custom formatting functions we do not need to
          // alter the remote JSON data, except to indicate that infinite
          // scrolling can be used
          params.page = params.page || 1;
    
          return {
            results: $.map(data.results, function(item) {
                return {id: item, text: '$zonePrefix' + item + '$zonePost'};
            }),
            pagination: {
              more: (params.page * 30) < data.total_count
            }
          };
        },
        cache: true
    },
    allowClear: true,
    placeholder: '{$placeholder}',
    language: '{$language}',
    minimumResultsForSearch: Infinity,
});
$('.{$this->getElementClassName()}').on('select2:select', function (e) {
    var data = e.params.data;
    console.log(data);
});
SCRIPT;

        Admin::script($script);
    }
    
    /**
    * {@inheritdoc}
    */
   public function render()
   {
       $this->setUpScripts();

       return <<<EOT
<div class="sidebar-form">
    <select class='form-control {$this->getElementClassName()}'>
    </select>
</div>
EOT;
   }
}
