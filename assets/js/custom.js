// Initialize Bootstrap dropdowns
document.addEventListener("DOMContentLoaded", () => {
  // Declare the bootstrap variable
  var bootstrap = window.bootstrap

  // Initialize all dropdowns
  var dropdownElementList = [].slice.call(document.querySelectorAll(".dropdown-toggle"))
  var dropdownList = dropdownElementList.map((dropdownToggleEl) => new bootstrap.Dropdown(dropdownToggleEl))

  // Fix for mobile menu
  var mobileBtn = document.getElementById("mobile_btn")
  if (mobileBtn) {
    mobileBtn.addEventListener("click", () => {
      var mainMenuWrapper = document.querySelector(".main-menu-wrapper")
      if (mainMenuWrapper) {
        mainMenuWrapper.classList.toggle("active")
      }
    })
  }

  var menuClose = document.getElementById("menu_close")
  if (menuClose) {
    menuClose.addEventListener("click", () => {
      var mainMenuWrapper = document.querySelector(".main-menu-wrapper")
      if (mainMenuWrapper) {
        mainMenuWrapper.classList.remove("active")
      }
    })
  }
})
