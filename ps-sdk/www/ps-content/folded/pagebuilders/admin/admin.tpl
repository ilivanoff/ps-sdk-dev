{if $authed}
    <!--PAGE START-->
    <div id="carrier">
        <!--ЗАГОЛОВОК-->

        <div id="header">
            <!--логотип-->
            {page_href code=$smarty.const.PAGE_ADMIN class="logo"}
            <img src="/ps-content/images/tools.png" alt="Панель администратора"/>
            {/page_href}

            <!--ссылки-->
            {$pagesLayout}

            <!--логотип-->
            <div class="adminControls">
                {page_href blank=1}.{/page_href}
                <a href="#" class="edit">Редактировать</a>
                {if $isBasic}
                    <a href="#" class="logout">Выход</a>
                {/if}
            </div>

            <div style="clear:both"></div>
        </div>
        {AdminPageNavigation::inst()->html()}
        <div id="adminPageContent" class="{$page->getPageIdent()}">
            <div class="adminPageContainer">
                {$content}
            </div>
        </div>
    </div>
{else}
    {literal}
        <style>
            body {
                background-color: #F0F0F0
            }

            .ps-external-auth {
                font-weight: bold;
                margin: 0px auto;
                padding: 20px 50px;
                text-align: center;
            }
        </style>
    {/literal}
    {if $isBasic}
        <div id="carrier" style="width: 200px; margin: 0px auto">
            {form form_id='AdminLoginForm'}
        </div>
    {else}
        <div class="ps-external-auth">
            Необходимо авторизоваться средствами используемой CMS
        </div>
    {/if}
{/if}