{foreach $pages as $page}
    <div class="popup-tool" data-type="{$page.type}" data-ident="{$page.ident}">
        <a href="{$page.url}" class="clickable"><img src="{$page.cover}"/></a>
        <div class="popup-tool-content">
            <h4><a href="{$page.url}" class="name">{$page.name}</a></h4>
                {text}{$page.descr}{/text}
        </div>
    </div>
{/foreach}