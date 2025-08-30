  // Dynamic copyright year
      document.querySelector(".copyright-year").textContent =
        new Date().getFullYear();

      // Back to top button
      const backToTopButton = document.getElementById("back-to-top");
      window.addEventListener("scroll", () => {
        backToTopButton.style.display =
          window.pageYOffset > 300 ? "block" : "none";
      });
      backToTopButton.addEventListener("click", () => {
        window.scrollTo({ top: 0, behavior: "smooth" });
      });

      // Form validation
      (function () {
        "use strict";
        const form = document.getElementById("contactForm");

        form.addEventListener(
          "submit",
          function (event) {
            if (!form.checkValidity()) {
              event.preventDefault();
              event.stopPropagation();
            }

            form.classList.add("was-validated");

            if (form.checkValidity()) {
              // Form submission logic would go here
              alert("Thank you for your message! We will contact you soon.");
              form.reset();
              form.classList.remove("was-validated");
            }
          },
          false
        );
      })();