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
              $nom.val(response.data.nom);
              $prenom.val(response.data.prenom);
              $email.val(response.data.email);
              $msg.text(response.data.message).css("color", "green").show();
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
