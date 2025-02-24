
    $("#zepto_config_btn").on("click",function(){
        $self = $(this);
        $.ajax({
            url: Craft.getUrl('admin/zeptomail/saveauthtoken'),
            type: 'POST',
            dataType: 'json',
            data: {
              'domain' : $('[name=zepto_domain]').val(),
              'mailtoken' : $('#zepto_mail_token').val(),
              'from_address' : $("#zepto_from_address").val(),
              'from_name' : $("#zepto_from_name").val() ,
              'CRAFT_CSRF_TOKEN' : $("[name=CRAFT_CSRF_TOKEN]").val()
            },
            beforeSend: function() {
							if($self.find(".loading-spinner").length === 0){
								$self.append($('<div>').addClass("loading-spinner"));
							}else {
								xhr.abort();
								return;
							}
            },
            success: function (data) {
              $self.find(".loading-spinner").remove();
							if(data.result === 'success'){
                addZeptoSuccessMessage('Plugin configured successfully');
              } else {
                addZeptoErrorMessage(data.message);
              }
            }
          });

    });
    $("#zepto_test_btn").on("click",function(){
      $self = $(this);
      $.ajax({
          url: Craft.getUrl('admin/zeptomail/testmail'),
          type: 'POST',
          dataType: 'json',
          data: {
            'CRAFT_CSRF_TOKEN' :  $("[name=CRAFT_CSRF_TOKEN]").val()
          },
          beforeSend: function() {
            if($self.find(".loading-spinner").length === 0){
              $self.append($('<div>').addClass("loading-spinner"));
            }else {
              xhr.abort();
              return;
            }
          },
          success: function (data) {
            $self.find(".loading-spinner").remove();
            if(data.result === 'success'){
              addZeptoSuccessMessage('Plugin configured successfully');
            } else {
              addZeptoErrorMessage(data.message);
            }
          }
        });

  });
  