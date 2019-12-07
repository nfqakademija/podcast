$('.blog').on("click", ".add", function () {
    var user_id = $(this).data("user-id");
    var url = $(this).data("url");
    var podcast_id = $(this).data("podcast-id");
    $(this).attr('class', 'et-beamed-note note-color remove');

    $.ajax
      ({
        type: "POST",
        url: url,
        data: {
          'podcast_id': podcast_id,
          'user_id': user_id,
          'action' : 'add'
        }
      });
  });
  
  $('.blog').on("click", ".remove", function () {
    var user_id = $(this).data("user-id");
    var url = $(this).data("url");
    var podcast_id = $(this).data("podcast-id");
    $(this).attr('class', 'et-beamed-note add');

    $.ajax
      ({
        type: "POST",
        url: url,
        data: {
          'podcast_id': podcast_id,
          'user_id': user_id,
          'action' : 'remove'
        }
      });
  });
  