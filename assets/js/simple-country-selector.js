// Sélecteur de pays simple avec drapeaux
class SimpleCountrySelector {
  constructor(container, options = {}) {
    this.container = container;
    this.options = {
      defaultCountry: "FR",
      placeholder: "Numéro de téléphone",
      ...options,
    };

    this.countries = [
      { code: "FR", name: "France", dial: "+33", flag: "🇫🇷" },
      { code: "BE", name: "Belgique", dial: "+32", flag: "🇧🇪" },
      { code: "CH", name: "Suisse", dial: "+41", flag: "🇨🇭" },
      { code: "CA", name: "Canada", dial: "+1", flag: "🇨🇦" },
      { code: "US", name: "États-Unis", dial: "+1", flag: "🇺🇸" },
      { code: "GB", name: "Royaume-Uni", dial: "+44", flag: "🇬🇧" },
      { code: "DE", name: "Allemagne", dial: "+49", flag: "🇩🇪" },
      { code: "ES", name: "Espagne", dial: "+34", flag: "🇪🇸" },
      { code: "IT", name: "Italie", dial: "+39", flag: "🇮🇹" },
      { code: "PT", name: "Portugal", dial: "+351", flag: "🇵🇹" },
      { code: "NL", name: "Pays-Bas", dial: "+31", flag: "🇳🇱" },
      { code: "DZ", name: "Algérie", dial: "+213", flag: "🇩🇿" },
      { code: "MA", name: "Maroc", dial: "+212", flag: "🇲🇦" },
      { code: "TN", name: "Tunisie", dial: "+216", flag: "🇹🇳" },
      { code: "SN", name: "Sénégal", dial: "+221", flag: "🇸🇳" },
      { code: "CI", name: "Côte d'Ivoire", dial: "+225", flag: "🇨🇮" },
      { code: "ML", name: "Mali", dial: "+223", flag: "🇲🇱" },
      { code: "BF", name: "Burkina Faso", dial: "+226", flag: "🇧🇫" },
      { code: "NE", name: "Niger", dial: "+227", flag: "🇳🇪" },
      { code: "TD", name: "Tchad", dial: "+235", flag: "🇹🇩" },
      { code: "CM", name: "Cameroun", dial: "+237", flag: "🇨🇲" },
      { code: "GA", name: "Gabon", dial: "+241", flag: "🇬🇦" },
      { code: "CG", name: "Congo", dial: "+242", flag: "🇨🇬" },
      { code: "CD", name: "RD Congo", dial: "+243", flag: "🇨🇩" },
      { code: "MG", name: "Madagascar", dial: "+261", flag: "🇲🇬" },
      { code: "MU", name: "Maurice", dial: "+230", flag: "🇲🇺" },
      { code: "RE", name: "Réunion", dial: "+262", flag: "🇷🇪" },
      { code: "YT", name: "Mayotte", dial: "+262", flag: "🇾🇹" },
      { code: "NC", name: "Nouvelle-Calédonie", dial: "+687", flag: "🇳🇨" },
      { code: "PF", name: "Polynésie française", dial: "+689", flag: "🇵🇫" },
      { code: "AU", name: "Australie", dial: "+61", flag: "🇦🇺" },
      { code: "NZ", name: "Nouvelle-Zélande", dial: "+64", flag: "🇳🇿" },
      { code: "JP", name: "Japon", dial: "+81", flag: "🇯🇵" },
      { code: "KR", name: "Corée du Sud", dial: "+82", flag: "🇰🇷" },
      { code: "CN", name: "Chine", dial: "+86", flag: "🇨🇳" },
      { code: "IN", name: "Inde", dial: "+91", flag: "🇮🇳" },
      { code: "TH", name: "Thaïlande", dial: "+66", flag: "🇹🇭" },
      { code: "VN", name: "Vietnam", dial: "+84", flag: "🇻🇳" },
      { code: "SG", name: "Singapour", dial: "+65", flag: "🇸🇬" },
      { code: "MY", name: "Malaisie", dial: "+60", flag: "🇲🇾" },
      { code: "ID", name: "Indonésie", dial: "+62", flag: "🇮🇩" },
      { code: "PH", name: "Philippines", dial: "+63", flag: "🇵🇭" },
      { code: "BR", name: "Brésil", dial: "+55", flag: "🇧🇷" },
      { code: "AR", name: "Argentine", dial: "+54", flag: "🇦🇷" },
      { code: "MX", name: "Mexique", dial: "+52", flag: "🇲🇽" },
      { code: "CL", name: "Chili", dial: "+56", flag: "🇨🇱" },
      { code: "CO", name: "Colombie", dial: "+57", flag: "🇨🇴" },
      { code: "PE", name: "Pérou", dial: "+51", flag: "🇵🇪" },
      { code: "VE", name: "Venezuela", dial: "+58", flag: "🇻🇪" },
      { code: "UY", name: "Uruguay", dial: "+598", flag: "🇺🇾" },
      { code: "PY", name: "Paraguay", dial: "+595", flag: "🇵🇾" },
      { code: "BO", name: "Bolivie", dial: "+591", flag: "🇧🇴" },
      { code: "EC", name: "Équateur", dial: "+593", flag: "🇪🇨" },
      { code: "GY", name: "Guyana", dial: "+592", flag: "🇬🇾" },
      { code: "SR", name: "Suriname", dial: "+597", flag: "🇸🇷" },
      { code: "GF", name: "Guyane française", dial: "+594", flag: "🇬🇫" },
      { code: "RU", name: "Russie", dial: "+7", flag: "🇷🇺" },
      { code: "UA", name: "Ukraine", dial: "+380", flag: "🇺🇦" },
      { code: "PL", name: "Pologne", dial: "+48", flag: "🇵🇱" },
      { code: "CZ", name: "République tchèque", dial: "+420", flag: "🇨🇿" },
      { code: "SK", name: "Slovaquie", dial: "+421", flag: "🇸🇰" },
      { code: "HU", name: "Hongrie", dial: "+36", flag: "🇭🇺" },
      { code: "RO", name: "Roumanie", dial: "+40", flag: "🇷🇴" },
      { code: "BG", name: "Bulgarie", dial: "+359", flag: "🇧🇬" },
      { code: "HR", name: "Croatie", dial: "+385", flag: "🇭🇷" },
      { code: "SI", name: "Slovénie", dial: "+386", flag: "🇸🇮" },
      { code: "BA", name: "Bosnie-Herzégovine", dial: "+387", flag: "🇧🇦" },
      { code: "RS", name: "Serbie", dial: "+381", flag: "🇷🇸" },
      { code: "ME", name: "Monténégro", dial: "+382", flag: "🇲🇪" },
      { code: "MK", name: "Macédoine du Nord", dial: "+389", flag: "🇲🇰" },
      { code: "AL", name: "Albanie", dial: "+355", flag: "🇦🇱" },
      { code: "GR", name: "Grèce", dial: "+30", flag: "🇬🇷" },
      { code: "TR", name: "Turquie", dial: "+90", flag: "🇹🇷" },
      { code: "CY", name: "Chypre", dial: "+357", flag: "🇨🇾" },
      { code: "MT", name: "Malte", dial: "+356", flag: "🇲🇹" },
      { code: "IS", name: "Islande", dial: "+354", flag: "🇮🇸" },
      { code: "NO", name: "Norvège", dial: "+47", flag: "🇳🇴" },
      { code: "SE", name: "Suède", dial: "+46", flag: "🇸🇪" },
      { code: "DK", name: "Danemark", dial: "+45", flag: "🇩🇰" },
      { code: "FI", name: "Finlande", dial: "+358", flag: "🇫🇮" },
      { code: "EE", name: "Estonie", dial: "+372", flag: "🇪🇪" },
      { code: "LV", name: "Lettonie", dial: "+371", flag: "🇱🇻" },
      { code: "LT", name: "Lituanie", dial: "+370", flag: "🇱🇹" },
      { code: "BY", name: "Biélorussie", dial: "+375", flag: "🇧🇾" },
      { code: "MD", name: "Moldavie", dial: "+373", flag: "🇲🇩" },
      { code: "AT", name: "Autriche", dial: "+43", flag: "🇦🇹" },
      { code: "LU", name: "Luxembourg", dial: "+352", flag: "🇱🇺" },
      { code: "LI", name: "Liechtenstein", dial: "+423", flag: "🇱🇮" },
      { code: "MC", name: "Monaco", dial: "+377", flag: "🇲🇨" },
      { code: "SM", name: "Saint-Marin", dial: "+378", flag: "🇸🇲" },
      { code: "VA", name: "Vatican", dial: "+39", flag: "🇻🇦" },
      { code: "AD", name: "Andorre", dial: "+376", flag: "🇦🇩" },
      { code: "IE", name: "Irlande", dial: "+353", flag: "🇮🇪" },
    ];

    this.selectedCountry =
      this.countries.find((c) => c.code === this.options.defaultCountry) ||
      this.countries[0];
    this.isOpen = false;

    this.init();
  }

  init() {
    this.render();
    this.bindEvents();
  }

  render() {
    // S'assurer que l'Algérie est sélectionnée par défaut
    if (!this.selectedCountry || this.selectedCountry.code !== "DZ") {
      this.selectedCountry =
        this.countries.find((c) => c.code === "DZ") || this.countries[0];
      console.log(
        "🔧 [SimpleCountrySelector] Pays par défaut forcé à:",
        this.selectedCountry
      );
    }

    this.container.innerHTML = `
      <div class="simple-phone-container" style="display: flex !important; align-items: center !important; border: 1px solid #d1d5db !important; border-radius: 8px !important; background: white !important; padding: 0 !important; margin: 0 !important; position: relative !important; z-index: 10000 !important; width: 100% !important; min-height: 48px !important; overflow: visible !important; clip: auto !important;">
        <div class="simple-country-selector" style="display: flex !important; align-items: center !important; padding: 8px 12px !important; cursor: pointer !important; border-right: 1px solid #e5e7eb !important; background: #f9fafb !important; border-radius: 8px 0 0 8px !important; min-width: 80px !important; position: relative !important; z-index: 10001 !important;">
          <span class="simple-flag" style="font-size: 16px !important; margin-right: 8px !important;">${
            this.selectedCountry.flag
          }</span>
          <span class="simple-dial" style="font-size: 14px !important; color: #374151 !important; font-weight: 500 !important;">${
            this.selectedCountry.dial
          }</span>
          <svg class="simple-arrow" width="12" height="8" viewBox="0 0 12 8" style="margin-left: 4px !important; transition: transform 0.2s ease !important;">
            <path d="M1 1.5L6 6.5L11 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <input type="tel" class="simple-phone-input" placeholder="${
          this.options.placeholder
        }" style="display: block !important; visibility: visible !important; flex: 1 !important; border: none !important; outline: none !important; padding: 8px 12px !important; font-size: 16px !important; background: transparent !important; color: #374151 !important;">
        <div class="simple-dropdown" style="display: none !important; position: absolute !important; top: 100% !important; left: 0 !important; right: 0 !important; background: white !important; border: 1px solid #e5e7eb !important; border-radius: 8px !important; box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important; z-index: 1002 !important; max-height: 200px !important; overflow: hidden !important;">
          <div class="simple-search" style="padding: 8px 12px !important; border-bottom: 1px solid #e5e7eb !important; background: #f9fafb !important; position: sticky !important; top: 0 !important; z-index: 1 !important;">
            <input type="text" class="simple-search-input" placeholder="Rechercher un pays..." style="width: 100% !important; padding: 8px 12px !important; border: 1px solid #d1d5db !important; border-radius: 4px !important; font-size: 14px !important; outline: none !important;">
          </div>
          <div class="simple-countries-list" style="max-height: 150px !important; overflow-y: auto !important; overflow-x: hidden !important; padding: 0 !important; margin: 0 !important; scrollbar-width: thin !important; scrollbar-color: #cbd5e1 #f1f5f9 !important;">
            ${this.countries
              .map(
                (country) => `
              <div class="simple-country-item" data-code="${country.code}" data-dial="${country.dial}" style="display: flex !important; align-items: center !important; padding: 6px 12px !important; cursor: pointer !important; border-bottom: 1px solid #f3f4f6 !important; transition: background-color 0.15s ease !important; min-height: 36px !important;">
                <span class="simple-country-flag" style="font-size: 16px !important; margin-right: 8px !important;">${country.flag}</span>
                <span class="simple-country-name" style="flex: 1 !important; font-size: 14px !important; color: #374151 !important;">${country.name}</span>
                <span class="simple-country-dial" style="font-size: 12px !important; color: #6b7280 !important; font-weight: 500 !important;">${country.dial}</span>
              </div>
            `
              )
              .join("")}
          </div>
        </div>
      </div>
    `;
  }

  bindEvents() {
    const selector = this.container.querySelector(".simple-country-selector");
    const dropdown = this.container.querySelector(".simple-dropdown");
    const searchInput = this.container.querySelector(".simple-search-input");
    const countryItems = this.container.querySelectorAll(
      ".simple-country-item"
    );
    const arrow = this.container.querySelector(".simple-arrow");

    // Toggle dropdown
    selector.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();

      console.log("🖱️ [SimpleCountrySelector] Clic sur le sélecteur");

      this.isOpen = !this.isOpen;

      if (this.isOpen) {
        dropdown.style.setProperty("display", "block", "important");
        dropdown.style.setProperty("opacity", "1", "important");
        dropdown.style.setProperty("visibility", "visible", "important");
        arrow.style.setProperty("transform", "rotate(180deg)", "important");
        console.log("📱 [SimpleCountrySelector] Dropdown: ouvert");
      } else {
        dropdown.style.setProperty("display", "none", "important");
        dropdown.style.setProperty("opacity", "0", "important");
        dropdown.style.setProperty("visibility", "hidden", "important");
        arrow.style.setProperty("transform", "rotate(0deg)", "important");
        console.log("📱 [SimpleCountrySelector] Dropdown: fermé");
      }
    });

    // Close dropdown when clicking outside
    document.addEventListener("click", (e) => {
      // Vérifier si le clic est à l'intérieur du container
      const isInsideContainer = this.container.contains(e.target);
      const isDropdownClick = dropdown.contains(e.target);
      const isSelectorClick = selector.contains(e.target);

      // Ne fermer que si le clic est vraiment à l'extérieur
      if (!isInsideContainer && !isDropdownClick && !isSelectorClick) {
        this.isOpen = false;
        dropdown.style.setProperty("display", "none", "important");
        dropdown.style.setProperty("opacity", "0", "important");
        dropdown.style.setProperty("visibility", "hidden", "important");
        arrow.style.setProperty("transform", "rotate(0deg)", "important");
        console.log(
          "📱 [SimpleCountrySelector] Dropdown fermé (clic extérieur)"
        );
      }
    });

    // Search functionality
    searchInput.addEventListener("input", (e) => {
      const query = e.target.value.toLowerCase();
      console.log("🔍 [SimpleCountrySelector] Recherche:", query);

      countryItems.forEach((item) => {
        const countryName = item
          .querySelector(".simple-country-name")
          .textContent.toLowerCase();
        const shouldShow = countryName.includes(query);
        item.style.setProperty(
          "display",
          shouldShow ? "flex" : "none",
          "important"
        );
      });
    });

    // Country selection
    countryItems.forEach((item) => {
      item.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();

        const code = item.dataset.code;
        const dial = item.dataset.dial;
        this.selectedCountry = this.countries.find((c) => c.code === code);

        // Update display
        const flagElement = this.container.querySelector(".simple-flag");
        const dialElement = this.container.querySelector(".simple-dial");

        if (flagElement) flagElement.textContent = this.selectedCountry.flag;
        if (dialElement) dialElement.textContent = this.selectedCountry.dial;

        // Close dropdown
        this.isOpen = false;
        dropdown.style.setProperty("display", "none", "important");
        dropdown.style.setProperty("opacity", "0", "important");
        dropdown.style.setProperty("visibility", "hidden", "important");
        arrow.style.setProperty("transform", "rotate(0deg)", "important");

        // Clear search
        searchInput.value = "";
        countryItems.forEach((item) => (item.style.display = "flex"));

        // Trigger event
        this.container.dispatchEvent(
          new CustomEvent("countryChanged", {
            detail: { country: this.selectedCountry },
          })
        );

        console.log(
          "🌍 [SimpleCountrySelector] Pays sélectionné:",
          this.selectedCountry.name
        );
      });
    });
  }

  getFullPhoneNumber() {
    const phoneInput = this.container.querySelector(".simple-phone-input");
    const phoneNumber = phoneInput.value.trim();
    if (!phoneNumber) return "";

    // Retourner le numéro complet avec le code pays
    return `${this.selectedCountry.dial} ${phoneNumber}`;
  }

  getCountryCode() {
    return this.selectedCountry.dial.replace("+", "");
  }

  getPhoneNumber() {
    const phoneInput = this.container.querySelector(".simple-phone-input");
    return phoneInput.value.trim();
  }

  setPhoneNumber(fullNumber) {
    if (!fullNumber) return;

    // Extract country code and number
    const match = fullNumber.match(/^(\+\d{1,4})\s?(.*)$/);
    if (match) {
      const [, countryCode, number] = match;
      const country = this.countries.find((c) => c.dial === countryCode);
      if (country) {
        this.selectedCountry = country;
        this.container.querySelector(".simple-flag").textContent = country.flag;
        this.container.querySelector(".simple-dial").textContent = country.dial;
      }
      this.container.querySelector(".simple-phone-input").value = number;
    } else {
      this.container.querySelector(".simple-phone-input").value = fullNumber;
    }
  }
}

// Export pour utilisation globale
window.SimpleCountrySelector = SimpleCountrySelector;

// Fonctions globales pour la validation
window.getPlanityCountryCode = function () {
  if (window.simpleCountrySelector) {
    return window.simpleCountrySelector.getCountryCode();
  }
  return "";
};

window.getPlanityPhoneNumber = function () {
  if (window.simpleCountrySelector) {
    return window.simpleCountrySelector.getFullPhoneNumber();
  }
  return "";
};
