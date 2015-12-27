<table class="colored database highlighted sortable">
    <thead>
        <tr>
            <th>Идентификатор</th>
            <th>ClPrefix</th>
            <th>SmPrefix</th>
            <th>Storable</th>
            <th>VisEnts</th>
            <th>Lists</th>
            <th>Path</th>
        </tr>
    </thead>
    <tbody>
        {foreach $foldings as $folding}
            <tr>
                <td>{$folding->getEntityName()} ({$folding->getUnique()})</td>
                <td>{$folding->getClassPrefix()}</td>
                <td>{$folding->getSmartyPrefix()}</td>
                <td>{if $folding instanceof StorableFolding}V{/if}</td>
                <td>{count($folding->getVisibleIdents())}</td>
                <td>{if $folding->hasLists()}{count($folding->getLists())}{/if}</td>
                <td>{$folding->getFoldingGroup()}</td>
            </tr>
        {/foreach}
    </tbody>
</table>
