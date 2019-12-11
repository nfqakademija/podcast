$('.listen-later').on("click", ".add", function () {
  var url = $(this).data("url");
  $(this).attr('class', 'et-beamed-note note-color remove');

  $.ajax
    ({
      type: "POST",
      url: url,
      data: {
        'action': 'add'
      }
    });
});

$('.listen-later').on("click", ".remove", function () {
  var url = $(this).data("url");
  $(this).attr('class', 'et-beamed-note add');

  $.ajax
    ({
      type: "POST",
      url: url,
      data: {
        'action': 'remove'
      }
    });
});
