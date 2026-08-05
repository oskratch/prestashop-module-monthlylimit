
/*
// Archivo original del modulo Monthly Limit
//
// En lugar de interceptar el clic de "añadir al carrito" (que depende del
// selector y del sistema de eventos concretos de cada theme: jQuery delegado
// en Classic, listeners nativos + fetch() en Hummingbird/PS9+), este script
// escucha las respuestas de red que ya genera el propio sitio y muestra el
// error cuando el hook actionCartUpdateQuantityBefore del backend bloquea
// una actualización de carrito.
//
// Es puramente aditivo: nunca hace preventDefault ni sustituye ningún
// manejador del theme, así que no hay riesgo de duplicar la petición ni de
// romper el comportamiento propio del theme, sea cual sea.
*/
(function () {
  var jQueryBound = false;

  var isMonthlyLimitError = function (data) {
    return data && data.hasError === true && Array.isArray(data.errors) && data.errors.length > 0;
  };

  var showErrorMessage = function (errors) {
    var errorContainer = document.querySelector('.custom-error-container');

    if (errorContainer) {
      errorContainer.innerText = errors.join('\n');
      errorContainer.classList.add('visible');
    } else {
      window.alert(errors.join('\n')); // Fallback si no hi ha un contenidor personalitzat per mostrar l'error
    }
  };

  var isSameOrigin = function (url) {
    try {
      return new URL(url, window.location.href).origin === window.location.origin;
    } catch (e) {
      return false;
    }
  };

  // Themes que usen fetch() nadiu (p. ex. Hummingbird / PrestaShop 9+)
  if (window.fetch) {
    var originalFetch = window.fetch;

    window.fetch = function (input, init) {
      var url = typeof input === 'string' ? input : (input && input.url);

      return originalFetch.apply(this, arguments).then(function (response) {
        if (url && isSameOrigin(url)) {
          response.clone().json()
            .then(function (data) {
              if (isMonthlyLimitError(data)) {
                showErrorMessage(data.errors);
              }
            })
            .catch(function () {
              // La resposta no és JSON, no ens interessa.
            });
        }

        return response;
      });
    };
  }

  // Classic theme i altres themes basats en jQuery ($.ajax / $.post)
  var bindJQuery = function () {
    if (jQueryBound || !window.jQuery) {
      return;
    }

    jQueryBound = true;

    window.jQuery(document).ajaxSuccess(function (event, xhr) {
      var data;

      try {
        data = JSON.parse(xhr.responseText);
      } catch (e) {
        return;
      }

      if (isMonthlyLimitError(data)) {
        showErrorMessage(data.errors);
      }
    });
  };

  bindJQuery();
  window.addEventListener('load', bindJQuery); // per si jQuery es carrega diferit
})();
