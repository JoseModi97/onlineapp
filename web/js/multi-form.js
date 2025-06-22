(function ( $ ) {
  $.fn.multiStepForm = function(args) {
      if(args === null || typeof args !== 'object' || $.isArray(args))
        throw  " : Called with Invalid argument";
      var form = this;
      var tabs = form.find('.tab');
      var steps = form.find('.step');
      steps.each(function(i, e){
        $(e).on('click', function(ev){
          form.navigateTo(i);
        });
      });
      form.navigateTo = function (i) {/*index*/
        /*Mark the current section with the class 'current'*/
        tabs.removeClass('current').eq(i).addClass('current');
        // Show only the navigation buttons that make sense for the current section:
        form.find('.previous').toggle(i > 0);
        atTheEnd = i >= tabs.length - 1;
        form.find('.next').toggle(!atTheEnd);
        // console.log('atTheEnd='+atTheEnd);
        form.find('.submit').toggle(atTheEnd);
        fixStepIndicator(curIndex());
        return form;
      }
      function curIndex() {
        /*Return the current index by looking at which section has the class 'current'*/
        return tabs.index(tabs.filter('.current'));
      }
      function fixStepIndicator(n) {
        steps.each(function(i, e){
          i == n ? $(e).addClass('active') : $(e).removeClass('active');
        });
      }
      /* Previous button is easy, just go back */
      form.find('.previous').click(function() {
        form.navigateTo(curIndex() - 1);
      });

      /* Next button goes forward if conditions (including optional beforeNext callback) are met */
      form.find('.next').click(function() {
        // Call beforeNext callback if provided
        if (typeof args.beforeNext === 'function') {
            // .call(form, ...) ensures 'this' inside beforeNext refers to the form element
            // It passes the current step index and the jQuery object for the current tab
            if (args.beforeNext.call(form, curIndex(), tabs.filter('.current')) === false) {
                return; // Stop processing if beforeNext returns false
            }
        }

        // Original plugin's own validation logic (we are not using this part with Yii)
        // This block is less relevant if beforeNext is designed to always return false for async validation.
        if('validations' in args && typeof args.validations === 'object' && !$.isArray(args.validations)){
          if(!('noValidate' in args) || (typeof args.noValidate === 'boolean' && !args.noValidate)){
            // Assuming form.validate and form.valid are from something like jQuery Validation plugin
            // form.validate(args.validations);
            // if(form.valid() == true){
            //   form.navigateTo(curIndex() + 1);
            //   return true; // Successfully navigated after plugin's validation
            // }
            // return false; // Plugin's validation failed

            // Simplified: if plugin has its own validation, it should handle navigation or stopping.
            // For our use case, we assume this block is not actively used with args.validations.
            // If it were, and if it failed, it should return false. If it passed, it would navigate.
          }
        }

        // If beforeNext didn't return false, and if the plugin's internal validation (if used) didn't stop it, then navigate.
        // For our specific async Yii validation:
        // - beforeNext will return false.
        // - The plugin's internal validation is not used.
        // - So, this line form.navigateTo(curIndex() + 1) will NOT be executed from this handler if beforeNext is used as planned.
        // If beforeNext is NOT provided, or returns true, this line WILL be executed.
        form.navigateTo(curIndex() + 1);
      });
      form.find('.submit').on('click', function(e){
        if(typeof args.beforeSubmit !== 'undefined' && typeof args.beforeSubmit !== 'function')
          args.beforeSubmit(form, this);
        /*check if args.submit is set false if not then form.submit is not gonna run, if not set then will run by default*/
        if(typeof args.submit === 'undefined' || (typeof args.submit === 'boolean' && args.submit)){
          form.submit();
        }
        return form;
      });
      /*By default navigate to the tab 0, if it is being set using defaultStep property*/
      typeof args.defaultStep === 'number' ? form.navigateTo(args.defaultStep) : null;

      form.noValidate = function() {

      }
      return form;
  };
}( jQuery ));
