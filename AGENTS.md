# AGENTS.md

Guidance for AI agents (Codex/ChatGPT) working in the Trying To Adult RVA repository.

---

## 🧰 Repository Overview
- **Site:** Full WordPress site for Trying To Adult RVA.
- **Primary Plugin:** `app/public/wp-content/plugins/tta-management-plugin`
  - Unless a task explicitly states otherwise, assume all questions and changes relate to this plugin.
  - The plugin must remain self‑contained so it can be copied to another WordPress installation and function on its own.

---

## 🧪 Development Environment
- **PHP:** Explicitly install PHP in the container before running any PHP or Composer commands.
- **Composer & PHPUnit:** Run from the plugin directory:
  ```bash
  cd app/public/wp-content/plugins/tta-management-plugin
  composer install
  vendor/bin/phpunit
  ```
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
Run Composer commands inside the plugin directory.

```bash
composer install                        # Install dependencies
composer update                         # Update dependencies and lock file
composer require authorizenet/authorizenet
composer require --dev phpunit/phpunit
```
