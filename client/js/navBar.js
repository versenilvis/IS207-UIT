//UI active class
const navLinks = document.querySelectorAll(".ph-nav-links a");
const currentPage = location.pathname.split("/").pop(); 

navLinks.forEach((link) => {
  const linkPage = link.getAttribute("href").split("/").pop();

  if (linkPage === currentPage) {
    link.classList.add("active");
  } else {
    link.classList.remove("active");
  }
});


//Drop down menu
const btn = document.getElementById("avatarBtn");
const dropdown = document.getElementById("dropdown");
btn.addEventListener("click", (e) => {
	e.stopPropagation();
	dropdown.classList.toggle("open");
});
document.addEventListener("click", () => dropdown.classList.remove("open"));
