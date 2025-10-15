'use strict';

// sidebar submenu collapsible js
document.querySelectorAll(".sidebar-menu .dropdown").forEach(function (dropdown) {
  dropdown.addEventListener("click", function () {
    var item = this;

    // Close all sibling dropdowns
    item.parentNode.querySelectorAll(".dropdown").forEach(function (sibling) {
      if (sibling !== item) {
        sibling.querySelector(".sidebar-submenu").style.display = 'none';
        sibling.classList.remove("dropdown-open");
        sibling.classList.remove("open");
      }
    });

    // Toggle the current dropdown
    var submenu = item.querySelector(".sidebar-submenu");
    submenu.style.display = (submenu.style.display === 'block') ? 'none' : 'block';

    item.classList.toggle("dropdown-open");
  });
});

// Toggle sidebar visibility and active class
const sidebarToggle = document.querySelector(".sidebar-toggle");
if(sidebarToggle) {
  sidebarToggle.addEventListener("click", function() {
    this.classList.toggle("active");
    document.querySelector(".sidebar").classList.toggle("active");
    document.querySelector(".dashboard-main").classList.toggle("active");
  });
}

// Open sidebar in mobile view and add overlay
const sidebarMobileToggle = document.querySelector(".sidebar-mobile-toggle");
if(sidebarMobileToggle) {
  sidebarMobileToggle.addEventListener("click", function() {
    document.querySelector(".sidebar").classList.add("sidebar-open");
    document.body.classList.add("overlay-active");
  });
}

// Close sidebar and remove overlay
const sidebarColseBtn = document.querySelector(".sidebar-close-btn");
if(sidebarColseBtn){
  sidebarColseBtn.addEventListener("click", function() {
    document.querySelector(".sidebar").classList.remove("sidebar-open");
    document.body.classList.remove("overlay-active");
  });
}

// to keep the current page active
document.addEventListener("DOMContentLoaded", function () {
  var nk = window.location.href;
  var links = document.querySelectorAll("ul#sidebar-menu a");

  links.forEach(function (link) {
    if (link.href === nk) {
      link.classList.add("active-page"); // anchor
      var parent = link.parentElement;
      parent.classList.add("active-page"); // li

      // Traverse up the DOM tree and add classes to parent elements
      while (parent && parent.tagName !== "BODY") {
        if (parent.tagName === "LI") {
          parent.classList.add("show");
          parent.classList.add("open");
        }
        parent = parent.parentElement;
      }
    }
  });
});

// ====== Force light mode ======
document.documentElement.classList.remove('dark');
localStorage.setItem('color-theme', 'light');

var themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
var themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');
var themeToggleBtn = document.getElementById('theme-toggle');

if(themeToggleDarkIcon) themeToggleDarkIcon.classList.add('hidden');
if(themeToggleLightIcon) themeToggleLightIcon.classList.remove('hidden');
if(themeToggleBtn) themeToggleBtn.style.display = 'none';
