"""
FunShirt - Testes Browser G8 (Playwright)
Painel de Estatisticas (Administracao)
Uso: python tests_g8_browser.py
"""
import os
import sys
import time

try:
    from playwright.sync_api import sync_playwright
except ImportError:
    print("Playwright nao instalado. A instalar...")
    import subprocess
    subprocess.run([sys.executable, "-m", "pip", "install", "playwright"], check=True)
    subprocess.run([sys.executable, "-m", "playwright", "install", "chromium"], check=True)
    from playwright.sync_api import sync_playwright

BASE_URL = "http://projeto.test"

results = []

def ok(test, msg=""):
    results.append(("PASS", test, msg))
    try:
        print(f"  [OK] {test}" + (f"\n       -> {msg}" if msg else ""))
    except Exception:
        print(f"  [OK] {test}")

def fail(test, msg=""):
    results.append(("FAIL", test, msg))
    try:
        print(f"  [FAIL] {test}" + (f"\n       -> {msg}" if msg else ""))
    except Exception:
        print(f"  [FAIL] {test}")

def section(title):
    print("\n" + "="*65)
    print(f"  TEST: {title}")
    print("="*65)

def login_as(page, email, password="123"):
    page.goto(f"{BASE_URL}/login")
    page.wait_for_load_state("networkidle")
    page.locator("input[name=email]").fill(email)
    page.locator("input[name=password]").fill(password)
    page.locator("button[type=submit]").click()
    page.wait_for_load_state("networkidle")
    time.sleep(0.8)

def logout(page):
    page.goto(BASE_URL)
    page.wait_for_load_state("networkidle")
    time.sleep(0.5)
    btn = page.locator("button:has-text('Sair'), a:has-text('Sair'), button:has-text('Logout'), a:has-text('Logout')")
    if btn.count() > 0:
        btn.first.click()
        page.wait_for_load_state("networkidle")
        time.sleep(0.8)
    else:
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

        # ----------------------------------------------------------------------
        section("T1 - Seguranca de Acesso (Anonimo e Cliente Comum)")
        # ----------------------------------------------------------------------
        try:
            # 1. Anónimo deve ser bloqueado
            page.goto(f"{BASE_URL}/admin/statistics")
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)
            
            content = page.content().lower()
            is_blocked = is_on_login(page) or "403" in page.title() or "forbidden" in content or "403" in content
            assert is_blocked, "Anonimo conseguiu aceder ao painel de estatisticas!"
            ok("Seguranca: Anonimo bloqueado corretamente")
        except Exception as e:
            fail("Seguranca: Anonimo bloqueado corretamente", str(e)[:120])

        try:
            # 2. Cliente comum deve ser bloqueado
            login_as(page, "c1@mail.pt", "123")
            page.goto(f"{BASE_URL}/admin/statistics")
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)
            
            content = page.content().lower()
            is_blocked = "403" in page.title() or "forbidden" in content or "403" in content or "/dashboard" in page.url or page.url == f"{BASE_URL}/"
            assert is_blocked, "Cliente comum acedeu ao painel de estatisticas!"
            ok("Seguranca: Cliente comum bloqueado corretamente")
            logout(page)
        except Exception as e:
            fail("Seguranca: Cliente comum bloqueado corretamente", str(e)[:120])

        # ----------------------------------------------------------------------
        section("T2 - Acesso do Administrador")
        # ----------------------------------------------------------------------
        try:
            login_as(page, "a2@mail.pt", "123")
            page.goto(f"{BASE_URL}/admin/statistics")
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)
            
            assert "painel de estatisticas" in page.content().lower() or "estatisticas" in page.content().lower(), "Titulo nao encontrado na pagina de administracao"
            ok("Acesso admin: Painel de estatisticas aberto com sucesso")
        except Exception as e:
            fail("Acesso admin: Painel de estatisticas aberto com sucesso", str(e)[:120])

        # ----------------------------------------------------------------------
        section("T3 - KPI Cards Principais & Presenca de Graficos")
        # ----------------------------------------------------------------------
        try:
            # KPIs
            content_lower = page.content().lower()
            assert "receita total" in content_lower, "KPI Receita Total em falta"
            assert "ticket medio" in content_lower, "KPI Ticket Medio em falta"
            assert "t-shirts vendidas" in content_lower, "KPI T-Shirts vendidas em falta"
            
            # Graficos Canvas
            assert page.locator("canvas#monthlyChart").count() == 1, "Grafico mensal (monthlyChart) nao carregado"
            assert page.locator("canvas#categoryChart").count() == 1, "Grafico de categorias (categoryChart) nao carregado"
            assert page.locator("canvas#designsChart").count() == 1, "Grafico de estampas (designsChart) nao carregado"
            assert page.locator("canvas#colorsChart").count() == 1, "Grafico de cores (colorsChart) nao carregado"
            
            ok("KPIs & Graficos: Todos os cards e canvases estao presentes no DOM")
        except Exception as e:
            fail("KPIs & Graficos: Todos os cards e canvases estao presentes no DOM", str(e)[:120])

        # ----------------------------------------------------------------------
        section("T4 - Funcionamento do Filtro Temporal por Ano")
        # ----------------------------------------------------------------------
        try:
            # Encontrar select de anos
            select_year = page.locator("select#year")
            assert select_year.count() == 1, "Dropdown de filtro de ano nao encontrado"
            
            # Selecionar "all" (Todos os Anos)
            select_year.select_option(value="all")
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)
            
            assert select_year.input_value() == "all", "Nao foi possivel filtrar por 'all'"
            ok("Filtro temporal: Dropdown funcional e permite selecionar 'Todos os Anos'")
        except Exception as e:
            fail("Filtro temporal: Dropdown funcional e permite selecionar 'Todos os Anos'", str(e)[:120])

        # ----------------------------------------------------------------------
        section("T5 - Tabelas de Extremos e Melhores Clientes")
        # ----------------------------------------------------------------------
        try:
            content_lower = page.content().lower()
            assert "melhores clientes" in content_lower, "Tabela de Melhores Clientes em falta"
            assert "extremos de encomendas" in content_lower, "Painel de Extremos de Encomendas em falta"
            
            # Verificar se os cards de extremos financeiros estao presentes
            assert "maior valor" in content_lower, "Indicador de Maior Valor em falta"
            assert "menor valor" in content_lower, "Indicador de Menor Valor em falta"
            assert "maior quantidade" in content_lower, "Indicador de Maior Quantidade em falta"
            
            ok("Tabelas de apoio: Melhores clientes e extremos de vendas visiveis")
            logout(page)
        except Exception as e:
            fail("Tabelas de apoio: Melhores clientes e extremos de vendas visiveis", str(e)[:120])

        browser.close()

        # ----------------------------------------------------------------------
        section("RESUMO FINAL - TESTES G8 BROWSER")
        # ----------------------------------------------------------------------
        total  = len(results)
        passed = sum(1 for r in results if r[0] == "PASS")
        failed = sum(1 for r in results if r[0] == "FAIL")

        print()
        for status, test, msg in results:
            suffix = f"\n       -> {msg}" if msg else ""
            print(f"  [{status}] {test}{suffix}")

        print()
        print(f"  " + "-"*50)
        print(f"  Total: {total} | Passou: {passed} | Falhou: {failed}")
        print(f"  " + "-"*50)
        print()

        if failed == 0:
            print("  ALL TESTS PASSED! G8 validation successful.")
        else:
            print(f"  FAILED: {failed} test(s) failed.")

        return failed == 0

if __name__ == "__main__":
    success = run_tests()
    sys.exit(0 if success else 1)
