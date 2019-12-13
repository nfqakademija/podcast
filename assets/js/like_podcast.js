$('.like').on("click", ".add-like", function () {
    var url = $(this).data("url");
    $(this).attr('class', 'et-heart heart-color remove-like');
  
    $.ajax
      ({
        type: "POST",
        url: url,
        data: {
          'action': 'add'
        }
      });
  });
  
  $('.like').on("click", ".remove-like", function () {
    var url = $(this).data("url");
    $(this).attr('class', 'et-heart-outlined add-like');
  
    $.ajax
      ({
        type: "POST",
        url: url,
        data: {
          'action': 'remove'
        }
      });
  });
  