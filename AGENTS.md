# AGENTS.md

Guidance for AI agents (Codex/ChatGPT) working in the `tta-management-plugin` repository.

---

## 🧰 Repository Overview
- **Name:** `tta-management-plugin`
- **Purpose:** A WordPress plugin for managing members, events, tickets, waitlists, notifications, and Authorize.Net payment integrations.

---

## 🧪 Development Environment
- **PHP:** Codex must explicitly install PHP into its container before execution. Assume PHP is not pre-installed; explicitly install it each time.
- **Composer:** Used for dependency management.
- **PHPUnit:** Used for running tests.  
- **WordPress:** Plugin runs in a WordPress environment; follow WP coding standards, hooks, actions, and filters.

---

## 🧠 Key Considerations for Agents

### 1. Documentation-first mindset
- Keep documentation in `/docs/` or README.md updated.
- Immediately document any new or altered functionality.

### 2. Caching layer
- The plugin implements caching (transients, object cache, or custom methods).
- Always read from and write to this caching layer. Do not bypass unless explicitly required.
- **Performance-critical queries:**  
  Consistently assess database queries. If a query seems likely to be executed frequently on the front-end, proactively integrate it into the caching layer.

### 3. Performance sensitivity
- Optimize all code for efficiency and performance.
- Minimize database queries, avoid unnecessary loops, and prioritize cache retrieval.

### 4. Security & data validation
- Sanitize and validate all inputs using WordPress built-in functions (`sanitize_text_field`, `wp_kses`, etc.).
- Properly handle JWTs, nonces, and secure API endpoints.
- **Payment Security:**  
  - **Never store customer payment details** (credit card numbers, CVV, etc.) directly in our database or within the plugin’s files.
  - Use Authorize.Net’s API securely, relying entirely on their secure tokenization methods and services for managing sensitive data.
  - Ensure all payment processing is PCI-compliant and follows best practices defined by Authorize.Net.

### 5. Coding standards
- Follow PSR-12 and WordPress PHP coding standards.
- Include PHPDoc comments for all public methods and classes.
- Use descriptive and concise commit messages.

---

## 🔄 Workflow & Processes

### Composer workflow
```bash
composer install                        # Install dependencies
composer update                         # Update dependencies and lock file
composer require authorizenet/authorizenet
composer require --dev phpunit/phpunit
```
