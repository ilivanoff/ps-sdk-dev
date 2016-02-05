<div id="carrier">
    <div id="mainPanel">
        <div id="leftPanel">
            <div id="content">
                {$content}
                {*task_test*}
            </div>
        </div>
        <div id="rightPanel">
            {text}
            {section start='1' loop=$cnt name='bar'}
                {$idx=$smarty.section.bar.index}
                {page_href code=$smarty.const.PAGE_TEST p_num=$idx}Тестовая страница {$idx}{/page_href}
            {/section}
            {/text}


            <br/>
            <h4>Специальные страницы</h4>
            {text}
            {page_href code=$smarty.const.PAGE_TEST p_pagetype='patterns'}Шаблоны{/page_href}
            {page_href code=$smarty.const.PAGE_TEST p_pagetype='testmethods'}Тестовые методы{/page_href}
            {page_href code=$smarty.const.PAGE_TEST p_pagetype='smarty'}Функции Smarty{/page_href}
            {/text}


            <br/>
            <h4>Картинки</h4>
            {text}
            {page_href code=$smarty.const.PAGE_TEST p_pagetype='doubleimg'}Дублирующиеся картинки{/page_href}
            {page_href code=$smarty.const.PAGE_TEST p_pagetype='imgbysize'}Картинки по весу{/page_href}
            {page_href code=$smarty.const.PAGE_TEST p_pagetype='formules'}Формулы{/page_href}
            {/text}

        </div>
        <div style="clear:both"></div>
    </div>
</div>