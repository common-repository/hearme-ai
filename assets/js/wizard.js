document.addEventListener('DOMContentLoaded', (event) => {
  if (window.location.href.indexOf('admin.php?page=hear-me-wizard') > -1) {
    const apiValue = document.querySelector('#api_key');
    const loading = document.createElement('div');
    const stagesTitles = document.querySelector('#wizard_title');
    const stagesText = document.querySelector('#wizard_step_text');
    const finishBtn = document.querySelector('#wizard_finish_button');
    const nextBtn = document.querySelector('#wizard_next');
    const form = document.querySelector('#wizard_form');
    const radioAll = document.querySelectorAll('.hear_me_wizard__step__radio_single input');
    const checkboxAll = document.querySelectorAll('.hear_me_wizard__step__checkbox_input');
    let stages = stagesTitles.textContent.split('|');
    let currentStage = 1;

    nextBtn.setAttribute('disabled', 'disabled');
    loading.innerHTML = '<div></div><div></div><div></div><div></div>';
    loading.classList = 'hm-ring';
    apiValue.after(loading);

    function hmdebounce(func, wait, immediate) {
      var timeout;
      return function() {
        var context = this, args = arguments;
        var later = function() {
          timeout = null;
          if (!immediate) func.apply(context, args);
        };
        var callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
      };
    };

    const updateStep = () => {
      stagesText.textContent = stages[0] + ' ' + currentStage.toString();

      stagesTitles.textContent = stages[currentStage];

      const currentStep = document.querySelector('#wizard_step_' + currentStage);
      const previousSteps = document.querySelectorAll('.hear_me_wizard__step');

      previousSteps.forEach((step) => {
        step.classList.remove('hear_me_wizard__step--shown');
      });

      currentStep.classList.add('hear_me_wizard__step--shown');

      if (currentStage == 4) {
        nextBtn.style.display = 'none';
        finishBtn.classList.add('hear_me_wizard__button--shown');
      }
    };

    radioAll.forEach((button) => {
      button.addEventListener('change', (e) => {
        radioAll.forEach((item) => {
          item.parentElement.classList.remove('hear_me_wizard__step__radio_label--checked');
        });
        button.parentElement.classList.add('hear_me_wizard__step__radio_label--checked');
      });
    });

    checkboxAll.forEach((button) => {
      if (button.checked == true) {
        button.parentElement.classList.add('hear_me_wizard__step__checkbox_label--checked');
      }

      button.addEventListener('change', (e) => {
        button.parentElement.classList.toggle('hear_me_wizard__step__checkbox_label--checked');
      });
    });

    var myEfficientFn = hmdebounce(function() {
      validateKey(apiValue.value);
    }, 250);

    apiValue.addEventListener('input', (e) => {
      myEfficientFn();        
    });

    updateStep();

    radioAll[0].checked = true;
    radioAll[0].parentElement.classList.add('hear_me_wizard__step__radio_label--checked');

    function validateKey(key) {
      loading.style.display = 'inline-block';
      const value = key;
      //api_url is data from php config file
      let apiUrl = api_url;

      if (value.length > 0) {
        var xmlHttp = new XMLHttpRequest();
        const apiValueError = document.querySelector('#api_key_error');
        xmlHttp.onreadystatechange = function () {
          if (xmlHttp.readyState === xmlHttp.DONE) {
            loading.style.display = 'none';
          }
          if (xmlHttp.readyState == 4 && xmlHttp.status == 200) {
            apiValueError.classList.remove('hear_me_wizard__step__api-error--active');
            finishBtn.classList.add('hear_me_wizard__button--shown');
            nextBtn.removeAttribute('disabled');
          }

          if (xmlHttp.readyState == 4 && xmlHttp.status == 404) {
            apiValueError.classList.add('hear_me_wizard__step__api-error--active');
            finishBtn.classList.remove('hear_me_wizard__button--shown');
            nextBtn.setAttribute('disabled', 'disabled');
            loading.style.display = 'none';
          }
        };
        xmlHttp.open('GET', apiUrl + 'echo?key=' + value, true);
        xmlHttp.send(null);
      }
    }
    nextBtn.onclick = () => {
      currentStage++;
      updateStep();
    };

    finishBtn.onclick = () => {
      if (currentStage != 4) {
        currentStage = 4;
        updateStep();
      } else {
        form.submit();
      }
    };
  }

  if (window.location.href.indexOf('admin.php?page=hear-me-settings') > -1) {
    const radioAll = document.querySelectorAll('.hear_me_wizard__step__radio_single input');
    radioAll.forEach((button) => {
      button.addEventListener('change', (e) => {
        radioAll.forEach((item) => {
          item.parentElement.classList.remove('hear_me_wizard__step__radio_label--checked');
        });
        button.parentElement.classList.add('hear_me_wizard__step__radio_label--checked');
      });
    });
  }
});
