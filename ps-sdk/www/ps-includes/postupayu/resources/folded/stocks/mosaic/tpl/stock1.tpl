{if $mode=='task'}
    Какое необычное животное изображено на рисунке и чем оно необычно?
{/if}

{if $mode=='ans'}
    <p>
        Это так называемая "стереокартинка", и чтобы увидеть её, нужно было немного
        постараться... На данной картинке изображён крылатый лев, который в природе встречается
        крайне редко:)
    </p>

    {stimgb name='lev.jpg'}
{/if}