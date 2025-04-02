
    $("#zepto_config_btn").on("click",function(){
        $self = $(this);
        $.ajax({
            url: Craft.getUrl(Craft.cpTrigger+'/zeptomail/saveauthtoken'),
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
                $("#zepto_mail_token").attr("disabled","disabled");
                $("#zepto_from_name").attr("disabled","disabled");
                $("#zepto_from_address").attr("disabled","disabled");
                $("[name=zepto_domain]").attr("disabled","disabled");
                $("#zepto_test_btn").removeClass("zm-dispNone");
                $("#zepto_config_btn").addClass("zm-dispNone");
                $('[purpose=zmail_modify_config]').removeClass("zm-dispNone");
              } else {
                addZeptoErrorMessage(data.message);
              }
            }
          });

    });
    $("#zepto_test_btn").on("click",function(){
      $self = $(this);
      $.ajax({
          url: Craft.getUrl(Craft.cpTrigger+'/zeptomail/testmail'),
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
  $("[purpose=reconfigure]").on("click",function(){
      $("#zepto_mail_token").removeAttr("disabled");
      $("#zepto_from_name").removeAttr("disabled");
      $("#zepto_from_address").removeAttr("disabled");
      $("[name=zepto_domain]").removeAttr("disabled");
      $("#zepto_test_btn").addClass("zm-dispNone");
      $("#zepto_config_btn").removeClass("zm-dispNone");
      $('[purpose=zmail_modify_config]').addClass("zm-dispNone");
  });
  