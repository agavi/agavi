<div class="tag-list">
    {section name=tag loop=$tags}
        {if $tags[tag].keyword != "abstract" &&
            $tags[tag].keyword != "access" &&
            $tags[tag].keyword != "static"}

            <h4>{$tags[tag].keyword|capitalize}:</h4>
            <div>{$tags[tag].data}</div>
        {/if}
    {/section}
</div>
