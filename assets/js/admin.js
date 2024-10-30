(($) => {
  $(document).ready(() => {
    $('.button-link.editinline').click(() => {
      quickEditEvent();
    });

    let quickEditEvent = () => {
      let timeout = setTimeout(() => {
        let postEditor = $('.inline-editor');
        let mainId = postEditor.attr('id').replace('edit-', 'post-');
        let postId = postEditor.attr('id').replace('edit-', '');
        let quickEditorUpdate = postEditor.find('.button.save');

        quickEditorUpdate.on('click', () => {
          let sendTimeout = setTimeout(() => {
            $.ajax({
              url: hear_me_settings.rest.generate_episode,
              method: 'POST',
              beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', hear_me_settings.nonce);
              },
              data: {
                id: postId,
              },
            }).done((generating) => {
              let interval = setInterval(() => {
                var xmlHttp = new XMLHttpRequest();
                xmlHttp.onreadystatechange = function () {
                  if (xmlHttp.readyState == 4 && xmlHttp.status == 200) {
                    clearInterval(interval);
                  }
                  if (xmlHttp.readyState == 4 && xmlHttp.status == 404) {
                    clearInterval(interval);
                  }
                };
                xmlHttp.open('GET', hear_me_settings.api.url + 'episode/' + postId + '?key=' + hear_me_settings.api.key, true);
                xmlHttp.send(null);
              }, 2000);

              $('#' + mainId)
                .find('.button-link.editinline')
                .click(() => {
                  quickEditEvent();
                });
            });
          }, 300);
        });
      }, 100);
    };

    $('.hear_me_generate_episode').on('click', (event) => {
      let postId = $(event.target).attr('data-post-id');
      $('.hear_me_step').hide();
      $('#hear_me_generating_episode').show();
      $.ajax({
        url: hear_me_settings.rest.publish_episode,
        method: 'POST',
        beforeSend: function (xhr) {
          xhr.setRequestHeader('X-WP-Nonce', hear_me_settings.nonce);
        },
        data: {
          id: postId,
        },
      }).done(() => {
        let interval = setInterval(() => {
          var xmlHttp = new XMLHttpRequest();
          xmlHttp.onreadystatechange = function () {
            if (xmlHttp.readyState == 4 && xmlHttp.status == 200) {
              let episodeInfo = JSON.parse(xmlHttp.response);
              if (episodeInfo.statusReason === null && episodeInfo.status === 'published') {
                $.ajax({
                  url: hear_me_settings.rest.get_player,
                  method: 'GET',
                  beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', hear_me_settings.nonce);
                  },
                  data: {
                    id: postId,
                  },
                }).done((player) => {
                  $('#hear_me_player_wrapper').html('<div id="hearme-player"></div>');
                  $('.hear_me_step').hide();
                  $('#hear_me_generated').show();
                  eval(player.script);
                  clearInterval(interval);
                });
              } else if (episodeInfo.statusReason !== null) {
                $('.hear_me_step').hide();
                $('.hear_me_edit_button').attr('href', episodeInfo.editorUrl);
                $('#hear_me_next_generate p').text(episodeInfo.statusReason);
                $('#hear_me_next_generate').show();
                clearInterval(interval);
              }
            }
          };
          xmlHttp.open('GET', hear_me_settings.api.url + 'episode/' + postId + '?key=' + hear_me_settings.api.key, true);
          xmlHttp.send(null);
        }, 2000);
      });
    });
  });
})(jQuery);
