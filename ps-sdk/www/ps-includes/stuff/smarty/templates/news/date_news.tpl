<div class="date_news"{* data-date={$block->getPickerDate()}*}>
    <h4 class="section red">
        {$block->getBlockDate()}
    </h4>
    <ol class="news_list">
        {foreach $block->getEvents() as $event}
            <li>
                <div class="news_item">
                    {$event->getPresentation()}
                </div>
            </li>
        {/foreach}
    </ol>
</div>