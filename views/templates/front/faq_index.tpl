<div class="block" id="faqs_block">
    <h3 class="page-product-heading">FAQs</h3>
    <div class="block_content list-block">
        <ul>
            {foreach $all_faqs as $key => $val}
                {$params['friendly_url'] = $val->friendly_url}
                <a href="{$link->getModuleLink('faqmodule', 'displayfaqs', $params)}" class="list-item"><li>{$val->question[1]}</li></a>
            {/foreach}
        </ul>
    </div>
</div>