$(document).ready(function() {
    var url = window.location.href;

    function getProductId(url) {
        var one = url.split('-');
        var two = one[0].split('/');
        var id_product = two[two.length - 1];
        return id_product;
    }

    $(document).on("click", "#add_question", function(e) {
        e.preventDefault();
        if ($("#questionField").val().length > 1) {
            $.ajax({
                method: "POST",
                url: "http://localhost/module/faqmodule/displayfaqs",
                data: {
                    question: $("#questionField").val(),
                    friendly_url: "add_question",
                    ajax: true,
                    id_product: getProductId(url)
                }
            }).done(function(data) {
                $("#addQuestionModal").modal("hide");
                $("#questionField").val("");
                alert(data);
            }).fail(function(data) {
                alert("Something went wrong!");
            });
        } else {
            alert("Question field is required!!!");
        }
    })
});
