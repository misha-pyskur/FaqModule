<div class="block" id="faqs_block">
    <h3 class="page-product-heading">FAQs</h3>
    <div class="block_content list-block">
        <ul class="list-group">
            {foreach $all_faqs as $key => $val}
                {$params['friendly_url'] = $val->friendly_url}
                {if $val->active === '1' && $val->friendly_url !== '' && $val->answer[1] !== ''}
                <a href="{$link->getModuleLink('faqmodule', 'displayfaqs', $params)}" class="list-group-item"><li>{$val->question[1]}</li></a>
            {/if}
            {/foreach}
        </ul>
    </div>
</div>