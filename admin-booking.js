jQuery(document).ready(function ($) {
  // Champ téléphone du formulaire d'ajout réservation (admin)
  var $phone = $("#add-booking-client-phone");
  var $nom = $("#add-booking-client-lastname");
  var $prenom = $("#add-booking-client-firstname");
  var $email = $("#add-booking-client-email");
  var $msg = $("#ib-booking-client-lookup-msg");

  if ($phone.length) {
    $phone.on("keydown", function (e) {
      if (e.key === "Enter") {
        e.preventDefault();
        var phoneVal = $phone.val();
        $.post(
          ibBookingAjax.ajax_url,
          {
            action: "lookup_client",
            phone: phoneVal,
            nonce: ibBookingAjax.nonce,
          },
          function (response) {
            if (response.success) {
              if (response.data.multiple) {
                // Plusieurs clientes : afficher une liste de sélection
                var html =
                  '<span style="color:#e9aebc;font-weight:600;">' +
                  response.data.message +
                  '</span><br><ul style="margin:8px 0 0 0;padding:0;list-style:none;">';
                response.data.clients.forEach(function (c, idx) {
                  html +=
                    '<li style="margin-bottom:6px;">' +
                    '<button type="button" class="ib-client-choice-btn" data-nom="' +
                    c.nom +
                    '" data-prenom="' +
                    c.prenom +
                    '" data-email="' +
                    c.email +
                    '" style="background:#fbeff2;color:#b95c8a;border-radius:8px;border:none;padding:6px 14px;cursor:pointer;">' +
                    c.name +
                    " (" +
                    c.email +
                    ")" +
                    "</button></li>";
                });
                html += "</ul>";
                $msg.html(html).show();
                $nom.val("");
                $prenom.val("");
                $email.val("");
                // Handler sur les boutons
                $(".ib-client-choice-btn")
                  .off("click")
                  .on("click", function () {
                    $nom.val($(this).data("nom"));
                    $prenom.val($(this).data("prenom"));
                    $email.val($(this).data("email"));
                    $msg
                      .text(
                        "Cliente sélectionnée : " +
                          $(this).data("nom") +
                          " " +
                          $(this).data("prenom")
                      )
                      .css("color", "green");
                  });
              } else {
                $nom.val(response.data.nom);
                $prenom.val(response.data.prenom);
                $email.val(response.data.email);
                $msg.text(response.data.message).css("color", "green").show();
              }
            } else {
              $msg.text(response.data.message).css("color", "red").show();
              $nom.val("");
              $prenom.val("");
              $email.val("");
            }
          }
        );
      }
    });
  }
});
