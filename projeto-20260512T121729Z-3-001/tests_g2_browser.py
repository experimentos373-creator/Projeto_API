"""
🧪 FunShirt — Testes Browser G2 (Playwright)
Catálogo Público e Administração (Categorias, Estampas, Cores e Preços)

Uso: python tests_g2_browser.py
"""
import os
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
TEST_IMAGE = os.path.abspath(os.path.join(
    os.path.dirname(__file__), 
    "projeto", "database", "seeders", "categories", "default_category.png"
))

results = []

def ok(test, msg=""):
    results.append(("✅ PASS", test, msg))
    print(f"  ✅ {test}" + (f"\n       ↳ {msg}" if msg else ""))

def fail(test, msg=""):
    results.append(("❌ FAIL", test, msg))
    print(f"  ❌ {test}" + (f"\n       ↳ {msg}" if msg else ""))

def section(title):
    print(f"\n{'─'*65}")
    print(f"  🧪 {title}")
    print(f"{'─'*65}")

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
    if not os.path.exists(TEST_IMAGE):
        print(f"❌ Imagem de teste não encontrada em: {TEST_IMAGE}")
        sys.exit(1)

    with sync_playwright() as p:
        headless_env = os.environ.get("HEADLESS", "False").lower() in ("true", "1", "yes")
        browser = p.chromium.launch(headless=headless_env, slow_mo=1000)
        ctx = browser.new_context()
        page = ctx.new_page()
        page.set_default_timeout(10000)

        # ══════════════════════════════════════════════════════════════════════
        section("T1 — Catálogo Público & Filtros (Anónimo)")
        # ══════════════════════════════════════════════════════════════════════
        try:
            page.goto(f"{BASE_URL}/")
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)

            # Verifica o título da loja
            assert "FunShirt" in page.content(), "Título da loja não encontrado"
            
            # Filtro por Nome (Estampa)
            page.locator("input[name=name]").fill("Lucky one")
            page.locator("button:has-text('Filtrar')").click()
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)
            
            assert "Lucky one" in page.content(), "Design 'Lucky one' deveria estar nos resultados"
            ok("Catálogo público: filtro por nome funcional")
        except Exception as e:
            fail("Catálogo público: filtro por nome funcional", str(e)[:120])

        # ══════════════════════════════════════════════════════════════════════
        section("T2 — Segurança de Rotas Administrativas (Anónimo/Cliente)")
        # ══════════════════════════════════════════════════════════════════════
        routes = ["/admin/categories", "/admin/tshirt-images", "/admin/colors", "/admin/prices/edit"]
        for route in routes:
            try:
                page.goto(f"{BASE_URL}{route}")
                page.wait_for_load_state("networkidle")
                time.sleep(0.4)
                
                # Deve ir para login ou dar 403
                content = page.content().lower()
                is_blocked = is_on_login(page) or "403" in page.title() or "forbidden" in content or "403" in content
                assert is_blocked, f"Acesso indevido à rota administrativa {route}"
                ok(f"Segurança: Anónimo bloqueado de {route}")
            except Exception as e:
                fail(f"Segurança: Anónimo bloqueado de {route}", str(e)[:120])

        try:
            # Login como Cliente Comum
            login_as(page, "c1@mail.pt", "123")
            for route in routes:
                page.goto(f"{BASE_URL}{route}")
                page.wait_for_load_state("networkidle")
                time.sleep(0.4)
                
                content = page.content().lower()
                is_blocked = "403" in page.title() or "forbidden" in content or "403" in content or "/dashboard" in page.url or page.url == f"{BASE_URL}/"
                assert is_blocked, f"Cliente acedeu à rota administrativa {route}"
                ok(f"Segurança: Cliente bloqueado de {route}")
            logout(page)
        except Exception as e:
            fail("Segurança: Cliente bloqueado das rotas admin", str(e)[:120])

        # ══════════════════════════════════════════════════════════════════════
        section("T3 — Gestão de Categorias (Admin)")
        # ══════════════════════════════════════════════════════════════════════
        try:
            login_as(page, "a2@mail.pt", "123")
            
            # Navega para categorias
            page.goto(f"{BASE_URL}/admin/categories")
            page.wait_for_load_state("networkidle")
            
            # Criar Categoria
            page.click("text=+ Nova Categoria")
            page.wait_for_load_state("networkidle")
            
            cat_name = "BrowserTestCat_" + "".join(random.choices(string.ascii_lowercase, k=4))
            page.fill("input[name=name]", cat_name)
            page.set_input_files("input[name=image_file]", TEST_IMAGE)
            page.click("button:has-text('Criar Categoria')")
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)

            # Pesquisa pela categoria criada para validar
            page.goto(f"{BASE_URL}/admin/categories?search={cat_name}")
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)
            assert cat_name in page.content(), "Categoria criada não listada"
            ok("Categoria: criação funcional", f"Nome: {cat_name}")

            # Editar Categoria
            # Encontra a linha com a nossa categoria e clica em Editar
            page.goto(f"{BASE_URL}/admin/categories?search={cat_name}")
            page.wait_for_load_state("networkidle")
            page.click("text=Editar")
            page.wait_for_load_state("networkidle")

            cat_name_updated = cat_name + "_Upd"
            page.fill("input[name=name]", cat_name_updated)
            page.click("button:has-text('Guardar Alterações')")
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)

            assert cat_name_updated in page.content(), "Categoria editada não listada"
            ok("Categoria: edição funcional", f"Novo nome: {cat_name_updated}")

            # Remover Categoria (Soft Delete)
            page.goto(f"{BASE_URL}/admin/categories?search={cat_name_updated}")
            page.wait_for_load_state("networkidle")
            
            # Configura escuta do confirm popup para aceitar
            page.once("dialog", lambda dialog: dialog.accept())
            page.click("button:has-text('Remover')")
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)

            assert cat_name_updated not in page.content(), "Categoria eliminada ainda listada"
            ok("Categoria: remoção (soft delete) funcional")
        except Exception as e:
            fail("Gestão de Categorias (Admin)", str(e)[:120])

        # ══════════════════════════════════════════════════════════════════════
        section("T4 — Gestão de Estampas (Admin)")
        # ══════════════════════════════════════════════════════════════════════
        try:
            page.goto(f"{BASE_URL}/admin/tshirt-images")
            page.wait_for_load_state("networkidle")
            
            # Criar Estampa
            page.click("text=+ Nova Estampa")
            page.wait_for_load_state("networkidle")
            
            print_name = "BrowserPrint_" + "".join(random.choices(string.ascii_lowercase, k=4))
            page.fill("input[name=name]", print_name)
            page.fill("textarea[name=description]", "Descrição de teste automatizado.")
            page.set_input_files("input[name=image_file]", TEST_IMAGE)
            page.click("button:has-text('Adicionar Design')")
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)

            # Pesquisa pela estampa criada para validar
            page.goto(f"{BASE_URL}/admin/tshirt-images?search={print_name}")
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)
            assert print_name in page.content(), "Estampa criada não listada"
            ok("Estampa: criação funcional", f"Nome: {print_name}")

            # Editar Estampa
            page.goto(f"{BASE_URL}/admin/tshirt-images?search={print_name}")
            page.wait_for_load_state("networkidle")
            page.click("text=Editar")
            page.wait_for_load_state("networkidle")

            print_name_upd = print_name + "_Upd"
            page.fill("input[name=name]", print_name_upd)
            page.click("button:has-text('Guardar Alterações')")
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)

            assert print_name_upd in page.content(), "Estampa editada não listada"
            ok("Estampa: edição funcional", f"Novo nome: {print_name_upd}")

            # Remover Estampa
            page.goto(f"{BASE_URL}/admin/tshirt-images?search={print_name_upd}")
            page.wait_for_load_state("networkidle")
            
            page.once("dialog", lambda dialog: dialog.accept())
            page.click("button:has-text('Remover')")
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)

            assert print_name_upd not in page.content(), "Estampa eliminada ainda listada"
            ok("Estampa: remoção funcional")
        except Exception as e:
            fail("Gestão de Estampas (Admin)", str(e)[:120])

        # ══════════════════════════════════════════════════════════════════════
        section("T5 — Gestão de Cores (Admin)")
        # ══════════════════════════════════════════════════════════════════════
        try:
            page.goto(f"{BASE_URL}/admin/colors")
            page.wait_for_load_state("networkidle")
            
            # Criar Cor
            page.click("text=+ Nova Cor")
            page.wait_for_load_state("networkidle")
            
            color_code = "f2" + "".join(random.choices("0123456789abcdef", k=4))
            color_name = "Cor Teste " + color_code
            
            page.fill("input[name=code]", color_code)
            page.fill("input[name=name]", color_name)
            page.set_input_files("input[name=tshirt_base_file]", TEST_IMAGE)
            page.click("button:has-text('Criar Cor')")
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)

            # Pesquisa pela cor criada para validar
            page.goto(f"{BASE_URL}/admin/colors?search={color_code}")
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)
            assert color_code in page.content(), "Cor criada não listada"
            ok("Cor: criação funcional", f"Código: #{color_code}")

            # Editar Cor
            page.goto(f"{BASE_URL}/admin/colors?search={color_code}")
            page.wait_for_load_state("networkidle")
            page.click("text=Editar")
            page.wait_for_load_state("networkidle")

            color_name_upd = color_name + " Upd"
            page.fill("input[name=name]", color_name_upd)
            page.click("button:has-text('Guardar Alterações')")
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)

            # Pesquisa pela cor editada para validar
            page.goto(f"{BASE_URL}/admin/colors?search={color_code}")
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)
            assert color_name_upd in page.content(), "Cor editada não listada"
            ok("Cor: edição funcional", f"Novo nome: {color_name_upd}")

            # Remover Cor
            page.goto(f"{BASE_URL}/admin/colors?search={color_code}")
            page.wait_for_load_state("networkidle")
            
            page.once("dialog", lambda dialog: dialog.accept())
            page.click("button:has-text('Remover')")
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)

            assert color_name_upd not in page.content(), "Cor eliminada ainda listada"
            ok("Cor: remoção funcional")
        except Exception as e:
            fail("Gestão de Cores (Admin)", str(e)[:120])

        # ══════════════════════════════════════════════════════════════════════
        section("T6 — Configuração de Preços (Admin)")
        # ══════════════════════════════════════════════════════════════════════
        try:
            page.goto(f"{BASE_URL}/admin/prices/edit")
            page.wait_for_load_state("networkidle")
            
            page.fill("input[name=unit_price_catalog]", "14.99")
            page.fill("input[name=unit_price_own]", "16.99")
            page.fill("input[name=unit_price_catalog_discount]", "11.99")
            page.fill("input[name=unit_price_own_discount]", "13.99")
            page.fill("input[name=qty_discount]", "8")
            
            page.click("button:has-text('Guardar Definições')")
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)

            assert "sucesso" in page.content().lower(), "Mensagem de sucesso não exibida"
            ok("Preços: atualização de registo único funcional")
        except Exception as e:
            fail("Configuração de Preços (Admin)", str(e)[:120])

        browser.close()

        # ══════════════════════════════════════════════════════════════════════
        section("📊 RESUMO FINAL — TESTES G2 BROWSER")
        # ══════════════════════════════════════════════════════════════════════
        total  = len(results)
        passed = sum(1 for r in results if r[0].startswith("✅"))
        failed = sum(1 for r in results if r[0].startswith("❌"))

        print()
        for status, test, msg in results:
            suffix = f"\n       ↳ {msg}" if msg else ""
            print(f"  {status} {test}{suffix}")

        print()
        print(f"  {'─'*50}")
        print(f"  Total: {total} | ✅ Passou: {passed} | ❌ Falhou: {failed}")
        print(f"  {'─'*50}")
        print()

        if failed == 0:
            print("  🎉  TODOS OS TESTES PASSARAM! G2 está VALIDADO no browser.")
        else:
            print(f"  🔴  {failed} teste(s) falharam. Ver detalhes acima.")

        return failed == 0

if __name__ == "__main__":
    success = run_tests()
    sys.exit(0 if success else 1)
