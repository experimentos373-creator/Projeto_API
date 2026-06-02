"""
🧪 FunShirt — Testes Browser G1 (Playwright)
Autenticação, Perfil e Gestão de Utilizadores

Utilizadores seed disponíveis:
  Admin : a2@mail.pt / 123
  Func  : f1@mail.pt / 123
  Cliente verificado: lucas.pereira@mail.pt / 123
  Cliente bloqueado: diana.branco@mail.pt / 123

Uso: python tests_g1_browser.py
"""
import sys
import time
import random
import string

try:
    from playwright.sync_api import sync_playwright
except ImportError:
    print("❌ Playwright não instalado. A instalar...")
    import subprocess
    subprocess.run([sys.executable, "-m", "pip", "install", "playwright"], check=True)
    subprocess.run([sys.executable, "-m", "playwright", "install", "chromium"], check=True)
    from playwright.sync_api import sync_playwright

BASE_URL = "http://projeto.test"

# ─── Resultados ───────────────────────────────────────────────────────────────
results = []

def ok(test, msg=""):
    results.append(("✅ PASS", test, msg))
    print(f"  ✅ {test}" + (f"\n       ↳ {msg}" if msg else ""))

def fail(test, msg=""):
    results.append(("❌ FAIL", test, msg))
    print(f"  ❌ {test}" + (f"\n       ↳ {msg}" if msg else ""))

def warn(test, msg=""):
    results.append(("⚠️  WARN", test, msg))
    print(f"  ⚠️  {test}" + (f"\n       ↳ {msg}" if msg else ""))

def section(title):
    print(f"\n{'─'*65}")
    print(f"  🧪 {title}")
    print(f"{'─'*65}")

def rand_email():
    rnd = ''.join(random.choices(string.ascii_lowercase, k=8))
    return f"test_{rnd}@funshirt.test"

def login_as(page, email, password="123"):
    """Utilitário: faz login com as credenciais dadas."""
    page.goto(f"{BASE_URL}/login")
    page.wait_for_load_state("networkidle")
    page.locator("input[name=email]").fill(email)
    page.locator("input[name=password]").fill(password)
    page.locator("button[type=submit]").click()
    page.wait_for_load_state("networkidle")
    time.sleep(0.8)

def logout(page):
    """Utilitário: faz logout."""
    page.goto(BASE_URL)
    page.wait_for_load_state("networkidle")
    time.sleep(0.5)
    # Tenta clicar no botão de logout
    btn = page.locator("button:has-text('Sair'), a:has-text('Sair'), button:has-text('Logout'), a:has-text('Logout'), form[action*='logout'] button")
    if btn.count() > 0:
        btn.first.click()
        page.wait_for_load_state("networkidle")
        time.sleep(0.8)
    else:
        # Fallback: POST para /logout via JavaScript
        page.evaluate("""
            fetch('/logout', {method:'POST', headers:{'X-XSRF-TOKEN': document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] || ''}})
        """)
        time.sleep(1)
        page.goto(BASE_URL)
        page.wait_for_load_state("networkidle")

def is_on_login(page):
    return "/login" in page.url or page.locator("input[name=email]").count() > 0

def run_tests():
    with sync_playwright() as p:
        headless_env = os.environ.get("HEADLESS", "False").lower() in ("true", "1", "yes")
        browser = p.chromium.launch(headless=headless_env, slow_mo=1000)
        ctx = browser.new_context()
        page = ctx.new_page()
        page.set_default_timeout(10000)

        # ══════════════════════════════════════════════════════════════════════
        section("T1 — Páginas Públicas Acessíveis (sem login)")
        # ══════════════════════════════════════════════════════════════════════
        for url, keyword in [
            (f"{BASE_URL}/",               "FunShirt"),
            (f"{BASE_URL}/login",          "Login"),
            (f"{BASE_URL}/register",       "Criar conta"),
            (f"{BASE_URL}/forgot-password","Recuperar"),
        ]:
            try:
                page.goto(url)
                page.wait_for_load_state("networkidle")
                content = page.content()
                assert keyword.lower() in content.lower() or page.locator(f"text={keyword}").count() > 0
                ok(f"GET {url.replace(BASE_URL,'')}", f"Contém '{keyword}'")
            except Exception as e:
                fail(f"GET {url.replace(BASE_URL,'')}", str(e)[:120])

        # ══════════════════════════════════════════════════════════════════════
        section("T2 — Rotas Protegidas Redirecionam para Login")
        # ══════════════════════════════════════════════════════════════════════
        for route in ["/dashboard", "/profile", "/admin/users"]:
            try:
                page.goto(f"{BASE_URL}{route}")
                page.wait_for_load_state("networkidle")
                time.sleep(0.4)
                assert is_on_login(page), f"URL final: {page.url}"
                ok(f"GET {route} → redireciona para /login")
            except Exception as e:
                fail(f"GET {route} → redireciona para /login", str(e)[:120])

        # ══════════════════════════════════════════════════════════════════════
        section("T3 — Registo de Novo Cliente")
        # ══════════════════════════════════════════════════════════════════════
        new_email = rand_email()
        try:
            page.goto(f"{BASE_URL}/register")
            page.wait_for_load_state("networkidle")

            page.locator("input[name=name]").fill("Cliente Browser Test")
            page.locator("input[name=email]").fill(new_email)
            page.locator("input[name=password]").fill("Password123!")
            page.locator("input[name=password_confirmation]").fill("Password123!")

            # Campo de género (se existir)
            gender = page.locator("select[name=gender]")
            if gender.count() > 0:
                gender.select_option("M")

            page.locator("button[type=submit]").click()
            page.wait_for_load_state("networkidle")
            time.sleep(1)

            current = page.url
            content = page.content().lower()
            is_verify = "/verify-email" in current or "verify" in content or "verificar" in content
            is_dashboard = "/dashboard" in current
            assert is_verify or is_dashboard, f"URL inesperada: {current}"
            ok("Registo cria novo utilizador", f"→ {current}")
        except Exception as e:
            fail("Registo cria novo utilizador", str(e)[:120])

        # ══════════════════════════════════════════════════════════════════════
        section("T4 — Email Verification Obrigatório")
        # ══════════════════════════════════════════════════════════════════════
        try:
            # Após o registo (T3), tentar aceder ao dashboard sem verificar email
            page.goto(f"{BASE_URL}/dashboard")
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)
            current = page.url
            content = page.content().lower()
            is_verify = "/verify-email" in current or "verify" in content or "verificar" in content
            assert is_verify, f"Deveria pedir verificação. URL: {current}"
            ok("Dashboard bloqueado sem verificação de email", f"URL: {current}")
        except Exception as e:
            fail("Dashboard bloqueado sem verificação de email", str(e)[:120])

        # ══════════════════════════════════════════════════════════════════════
        section("T5 — Login com Admin")
        # ══════════════════════════════════════════════════════════════════════
        try:
            logout(page)
            login_as(page, "a2@mail.pt", "123")
            current = page.url
            assert "/login" not in current, f"Login falhou. URL: {current}"
            ok("Login Admin (a2@mail.pt)", f"→ {current}")
        except Exception as e:
            fail("Login Admin", str(e)[:120])

        # ══════════════════════════════════════════════════════════════════════
        section("T6 — Admin: Acesso ao Painel de Utilizadores")
        # ══════════════════════════════════════════════════════════════════════
        try:
            page.goto(f"{BASE_URL}/admin/users")
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)
            content = page.content().lower()
            current = page.url
            is_404 = "404" in page.title()
            is_login_redirect = "/login" in current
            assert not is_404 and not is_login_redirect, f"Acesso negado/404. URL: {current}"
            ok("Admin acede a /admin/users", f"URL: {current}")
        except Exception as e:
            fail("Admin acede a /admin/users", str(e)[:120])

        # ══════════════════════════════════════════════════════════════════════
        section("T7 — Admin: Perfil Editável")
        # ══════════════════════════════════════════════════════════════════════
        try:
            page.goto(f"{BASE_URL}/profile")
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)
            current = page.url
            content = page.content().lower()
            assert "/login" not in current, "Redirecionou para login"
            has_form = "input" in content or "form" in content
            assert has_form, "Não encontrou formulário de perfil"
            ok("Perfil acessível (admin)", f"URL: {current}")
        except Exception as e:
            fail("Perfil acessível (admin)", str(e)[:120])

        # ══════════════════════════════════════════════════════════════════════
        section("T8 — Logout do Admin")
        # ══════════════════════════════════════════════════════════════════════
        try:
            logout(page)
            time.sleep(0.5)
            page.goto(f"{BASE_URL}/profile")
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)
            assert is_on_login(page), f"Sessão ainda ativa após logout. URL: {page.url}"
            ok("Logout Admin + sessão inválida", f"URL após logout+profile: {page.url}")
        except Exception as e:
            fail("Logout Admin + sessão inválida", str(e)[:120])

        # ══════════════════════════════════════════════════════════════════════
        section("T9 — Login com Funcionario + Controlo de Acesso")
        # ══════════════════════════════════════════════════════════════════════
        # Nota: f1@mail.pt esta bloqueado na seed — usar f2@mail.pt (nao bloqueado)
        try:
            login_as(page, "f2@mail.pt", "123")
            current_after = page.url
            assert "/login" not in current_after, f"Login Funcionário falhou. URL: {current_after}"
            ok("Login Funcionário (f2@mail.pt)", f"→ {current_after}")

            # Funcionário NÃO deve aceder a /admin/users
            page.goto(f"{BASE_URL}/admin/users")
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)
            admin_url = page.url
            admin_content = page.content()
            is_blocked = (
                "403" in admin_content or
                "forbidden" in admin_content.lower() or
                admin_url in [f"{BASE_URL}/", BASE_URL] or
                "/login" in admin_url or
                "/dashboard" in admin_url
            )
            if is_blocked:
                ok("Funcionario bloqueado de /admin/users", f"URL: {admin_url}")
            else:
                fail("Funcionario bloqueado de /admin/users", f"Acedeu sem permissao! URL: {admin_url}")
        except Exception as e:
            fail("Login Funcionario / controlo acesso", str(e)[:120])

        # ══════════════════════════════════════════════════════════════════════
        section("T10 — Login com Cliente Verificado + Controlo de Acesso")
        # ══════════════════════════════════════════════════════════════════════
        try:
            logout(page)
            login_as(page, "lucas.pereira@mail.pt", "123")
            current_c = page.url
            ok("Login Cliente verificado", f"→ {current_c}")

            # Cliente NÃO deve aceder a /admin/users
            page.goto(f"{BASE_URL}/admin/users")
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)
            c_admin_url = page.url
            c_content = page.content()
            is_blocked_c = (
                "403" in c_content or
                "forbidden" in c_content.lower() or
                c_admin_url in [f"{BASE_URL}/", BASE_URL] or
                "/login" in c_admin_url
            )
            if is_blocked_c:
                ok("Cliente bloqueado de /admin/users", f"URL: {c_admin_url}")
            else:
                fail("Cliente bloqueado de /admin/users", f"Acedeu sem permissão! URL: {c_admin_url}")
        except Exception as e:
            fail("Login Cliente / controlo acesso", str(e)[:120])

        # ══════════════════════════════════════════════════════════════════════
        section("T11 — Cliente Bloqueado Não Consegue Entrar")
        # ══════════════════════════════════════════════════════════════════════
        try:
            logout(page)
            login_as(page, "diana.branco@mail.pt", "123")  # blocked=1
            current_blocked = page.url
            content_blocked = page.content().lower()
            is_denied = (
                "/login" in current_blocked or
                "bloqueado" in content_blocked or
                "blocked" in content_blocked or
                "suspended" in content_blocked
            )
            if is_denied:
                ok("Cliente bloqueado não consegue entrar", f"URL: {current_blocked}")
            else:
                fail("Cliente bloqueado não consegue entrar", f"Entrou! URL: {current_blocked}")
        except Exception as e:
            fail("Cliente bloqueado não consegue entrar", str(e)[:120])

        # ══════════════════════════════════════════════════════════════════════
        section("T12 — Recuperação de Senha (Forgot Password)")
        # ══════════════════════════════════════════════════════════════════════
        try:
            logout(page)
            page.goto(f"{BASE_URL}/forgot-password")
            page.wait_for_load_state("networkidle")
            time.sleep(0.4)

            email_input = page.locator("input[name=email]")
            assert email_input.count() > 0, "Campo de email não encontrado"
            email_input.fill("lucas.pereira@mail.pt")

            submit_btn = page.locator("button[type=submit]")
            assert submit_btn.count() > 0, "Botão de submissão não encontrado"
            submit_btn.click()
            page.wait_for_load_state("networkidle")
            time.sleep(1)

            content_reset = page.content().lower()
            success_indicators = ["enviado", "sent", "link", "email", "password"]
            found = any(word in content_reset for word in success_indicators)
            assert found, "Nenhuma mensagem de confirmação encontrada"
            ok("Forgot password: formulário funciona", "Mensagem de confirmação encontrada")
        except Exception as e:
            fail("Forgot password: formulário funciona", str(e)[:120])

        browser.close()

        # ══════════════════════════════════════════════════════════════════════
        section("📊 RESUMO FINAL — TESTES G1 BROWSER")
        # ══════════════════════════════════════════════════════════════════════
        total  = len(results)
        passed = sum(1 for r in results if r[0].startswith("✅"))
        warns  = sum(1 for r in results if r[0].startswith("⚠️"))
        failed = sum(1 for r in results if r[0].startswith("❌"))

        print()
        for status, test, msg in results:
            suffix = f"\n       ↳ {msg}" if msg else ""
            print(f"  {status} {test}{suffix}")

        print()
        print(f"  {'─'*50}")
        print(f"  Total: {total} | ✅ Passou: {passed} | ⚠️  Aviso: {warns} | ❌ Falhou: {failed}")
        print(f"  {'─'*50}")
        print()

        if failed == 0:
            print("  🎉  TODOS OS TESTES PASSARAM! G1 está VALIDADO no browser.")
        else:
            print(f"  🔴  {failed} teste(s) falharam. Ver detalhes acima e corrigir.")

        return failed == 0

if __name__ == "__main__":
    success = run_tests()
    sys.exit(0 if success else 1)
