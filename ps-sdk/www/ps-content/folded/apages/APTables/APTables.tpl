{if $mode=='list'}
    {*Табы со скоупами*}
    <div id="APTables-tab" class="ps-tabs">
        {*Данные*}
        {foreach $data as $type => $content}
            <div title="{$type}" class="tab" data-type="{$type}">
                <textarea codemirror="scheme">{$content}</textarea>
            </div>
        {/foreach}
    </div>

    <div class="controls">
        <button>Сохранить</button>
        <button>Перезагрузить</button>
    </div>
{/if}
