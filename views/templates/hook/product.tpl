<div class="block" id="product_faqs_block">
    <h3 class="page-product-heading">FAQs</h3>
    <div class="block_content list-block">
        <ul class="list-group">
            {foreach $faqs as $key => $val}
                {$params['friendly_url'] = $val->friendly_url}
                {if $val->active === '1' && $val->friendly_url !== '' && $val->answer[1] !== ''}
                    <a href="{$link->getModuleLink('faqmodule', 'displayfaqs', $params)}" class="list-group-item"><li>{$val->question[1]}</li></a>
                {/if}
            {/foreach}
        </ul>
    </div>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addQuestionModal">
        Add Question
    </button>
</div>

<!-- Modal -->
<div class="modal fade" id="addQuestionModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Add Question</h4>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="questionField">Question</label>
                        <textarea class="form-control" id="questionField" rows="6"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" id="add_question" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>