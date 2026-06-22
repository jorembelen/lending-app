# GitHub Copilot Instructions

## Developer Profile

I am a senior Laravel developer using **Laravel 10**, **Livewire 2.12**, **Blade templates**, and **Bootstrap** for frontend. I follow **PSR standards** and **clean code principles**. My architecture separates business logic into service classes and keeps Blade templates lean. I use **Alpine.js** only for simple UI interactions.

---

## Copilot Behavior

### ✅ What to Do

- Provide **clean, complete Laravel code** using Livewire 2.12 best practices  
- Use **Blade templates** and **Bootstrap 5** for all UI  
- Write **reusable, modular Blade components**  
- Organize business logic in **service or action classes**  
- Ensure **responsive design** and **accessibility**  
- Prefer **Eloquent** and **Collection methods** for brevity and readability  
- Follow **PSR naming conventions** and **Laravel-style syntax**  
- Use proper **validation**, **error handling**, and **authorization**  
- Optimize for **performance**, **scalability**, and **maintainability**  

### ❌ What to Avoid

- Tailwind CSS  
- Vue.js or any JS frameworks other than Alpine.js  
- Inline or complex logic in Blade views  
- Deprecated Laravel or Livewire methods  
- Verbose or redundant comments  
- JavaScript-heavy solutions outside Alpine.js  

---

## Focus Areas

- Responsive UIs built with **Bootstrap**  
- Simple, **maintainable Livewire components**  
- Optimized **database queries** with **pagination** and **caching**  
- **DRY principles** and clear separation of concerns  
- **Testable, production-ready code**  
- Clear naming, scoped CSS, minimal JavaScript  
- Concise form validation using **FormRequest** or **Livewire rules**  
- Leverage Laravel features: **Policies**, **Events**, **Jobs**, **Service Container**, etc.  

---

## Final Expectations

- Remove legacy or unnecessary code  
- Clean up unused Blade components, views, and classes  
- Ensure **backward compatibility** where applicable  
- Document **reusable components** and **services**  
- Maintain a **tidy and scalable** codebase for future development  
- Always reference the project's default template styles via `<link href="{{ asset('assets/css/custom.min.css') }}" rel="stylesheet" type="text/css">` when implementing UI components
- Follow the existing design patterns and CSS classes defined in the custom stylesheet to maintain visual consistency
- Use the established color scheme, spacing, and component styles from the default template
- Ensure all new components integrate seamlessly with the existing design system
- Prioritize extending existing custom styles over creating new ones to maintain design cohesion

