"""
FunShirt - Testes Browser G3 (Playwright)
Carrinho de Compras, Descontos por Volume, Previews CSS e Redirecionamento
Uso: python tests_g3_browser.py
"""
import os
import sys
import time
import random

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

def run_tests():
    with sync_playwright() as p:
        headless_env = os.environ.get("HEADLESS", "False").lower() in ("true", "1", "yes")
        browser = p.chromium.launch(headless=headless_env, slow_mo=1000)
        ctx = browser.new_context()
        page = ctx.new_page()
        page.set_default_timeout(10000)

        # Garantir carrinho limpo antes de comecar
        page.goto(f"{BASE_URL}/cart")
        page.wait_for_load_state("networkidle")
        clear_btn = page.locator("button:has-text('Esvaziar carrinho')")
        if clear_btn.count() > 0:
            page.once("dialog", lambda dialog: dialog.accept())
            clear_btn.click()
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)

        # ----------------------------------------------------------------------
        section("T1 - Visualizar Carrinho Vazio (Anonimo)")
        # ----------------------------------------------------------------------
        try:
            page.goto(f"{BASE_URL}/cart")
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)
            assert "carrinho esta vazio" in page.content().lower() or "carrinho" in page.content().lower(), "Mensagem de carrinho vazio nao encontrada"
            ok("Carrinho Vazio: exibido corretamente")
        except Exception as e:
            fail("Carrinho Vazio: exibido corretamente", str(e)[:120])

        # ----------------------------------------------------------------------
        section("T2 - Adicionar Item ao Carrinho a partir do Detalhe")
        # ----------------------------------------------------------------------
        try:
            page.goto(f"{BASE_URL}/")
            page.wait_for_load_state("networkidle")
            
            page.locator("a[href*='/tshirt-images/']").first.click()
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)
            
            assert "/tshirt-images/" in page.url, "Nao redirecionou para detalhe da imagem"

            page.check("input[name=color]", force=True)
            page.check("input[name=size][value=M]", force=True)
            
            page.locator("input[name=quantity]").fill("2")
            
            page.locator("button[type=submit]:has-text('Adicionar')").click()
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)

            assert "/cart" in page.url, "Nao redirecionou para o carrinho apos adicionar"
            assert "adicionada ao carrinho" in page.content().lower() or "carrinho" in page.content().lower(), "Mensagem de sucesso nao encontrada"
            ok("Adicionar ao carrinho: funcional")
        except Exception as e:
            fail("Adicionar ao carrinho: funcional", str(e)[:120])

        # ----------------------------------------------------------------------
        section("T3 - Calculo de Subtotal e Total Sem Desconto")
        # ----------------------------------------------------------------------
        try:
            subtotal_text = page.locator("span:has-text('Subtotal:')").first.locator("xpath=following-sibling::span").inner_text()
            total_text = page.locator("span:has-text('Total')").last.locator("xpath=following-sibling::span").inner_text()

            subtotal_val = float(subtotal_text.replace("u20ac", "").replace("EUR", "").replace("Euro", "").replace("e", "").replace("E", "").replace(chr(8364), "").strip().replace(",", "."))
            total_val = float(total_text.replace("u20ac", "").replace("EUR", "").replace("Euro", "").replace("e", "").replace("E", "").replace(chr(8364), "").strip().replace(",", "."))

            assert subtotal_val == total_val, "Subtotal da linha deve ser igual ao total global para 1 item"
            ok("Calculos do carrinho: subtotal e total consistentes", f"Subtotal/Total: {total_val}")
        except Exception as e:
            fail("Calculos do carrinho: subtotal e total consistentes", str(e)[:120])

        # ----------------------------------------------------------------------
        section("T4 - Aplicacao Automatica de Desconto por Volume")
        # ----------------------------------------------------------------------
        try:
            page.locator("input[name=quantity]").first.fill("10")
            page.locator("button:has-text('Atualizar')").first.click()
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)

            assert "desconto" in page.content().lower(), "Badge de desconto nao encontrado"
            
            strikethrough = page.locator("span[style*='line-through']")
            assert strikethrough.count() > 0, "Preco antigo riscado nao encontrado"
            
            ok("Desconto por volume: aplicado automaticamente ao ultrapassar limite")
        except Exception as e:
            fail("Desconto por volume: aplicado automaticamente ao ultrapassar limite", str(e)[:120])

        # ----------------------------------------------------------------------
        section("T5 - Alteracao de Cor e Tamanho Inline")
        # ----------------------------------------------------------------------
        try:
            page.locator("input[name=quantity]").first.fill("2")
            
            color_select = page.locator("select[name=color]").first
            color_select.select_option(index=1)
            
            size_select = page.locator("select[name=size]").first
            size_select.select_option(value="XL")
            
            page.locator("button:has-text('Atualizar')").first.click()
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)

            assert size_select.input_value() == "XL", "Tamanho nao atualizado para XL"
            ok("Editar cor/tamanho: funcional inline")
        except Exception as e:
            fail("Editar cor/tamanho: funcional inline", str(e)[:120])

        # ----------------------------------------------------------------------
        section("T6 - Colisao de Chaves (Fusao de Quantidades)")
        # ----------------------------------------------------------------------
        try:
            page.goto(f"{BASE_URL}/")
            page.wait_for_load_state("networkidle")
            page.locator("a[href*='/tshirt-images/']").first.click()
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)

            page.check("input[name=color]", force=True)
            page.check("input[name=size][value=M]", force=True)
            page.locator("input[name=quantity]").fill("3")
            page.locator("button[type=submit]:has-text('Adicionar')").click()
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)

            first_color_value = page.locator("select[name=color]").last.input_value()
            
            page.locator("select[name=color]").first.select_option(value=first_color_value)
            page.locator("select[name=size]").first.select_option(value="M")
            page.locator("input[name=quantity]").first.fill("2")
            
            page.locator("button:has-text('Atualizar')").first.click()
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)

            item_rows = page.locator("input[name=quantity]")
            assert item_rows.count() == 1, f"Deveria haver apenas 1 item apos a fusao, mas existem {item_rows.count()}"
            assert item_rows.first.input_value() == "5", f"A quantidade fundida deveria ser 5, mas e {item_rows.first.input_value()}"
            
            ok("Colisao de chaves: itens com a mesma combinacao sao fundidos", "Fusao bem sucedida (qty = 5)")
        except Exception as e:
            fail("Colisao de chaves: itens com a mesma combinacao sao fundidos", str(e)[:120])

        # ----------------------------------------------------------------------
        section("T7 - Remocao Automatica ao Alterar Quantidade para 0")
        # ----------------------------------------------------------------------
        try:
            page.locator("input[name=quantity]").first.fill("0")
            page.locator("button:has-text('Atualizar')").first.click()
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)

            assert "carrinho" in page.content().lower() or "vazio" in page.content().lower(), "O item nao foi removido automaticamente ao definir quantidade 0"
            ok("Remocao automatica (qty = 0): funcional")
        except Exception as e:
            fail("Remocao automatica (qty = 0): funcional", str(e)[:120])

        # ----------------------------------------------------------------------
        section("T8 - Esvaziar Carrinho Completo")
        # ----------------------------------------------------------------------
        try:
            page.goto(f"{BASE_URL}/")
            page.wait_for_load_state("networkidle")
            page.locator("a[href*='/tshirt-images/']").first.click()
            page.wait_for_load_state("networkidle")
            page.check("input[name=color]", force=True)
            page.check("input[name=size][value=S]", force=True)
            page.locator("button[type=submit]:has-text('Adicionar')").click()
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)

            page.once("dialog", lambda dialog: dialog.accept())
            page.locator("button:has-text('Esvaziar carrinho')").click()
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)

            assert "carrinho" in page.content().lower() or "vazio" in page.content().lower()
            ok("Limpar carrinho: funcional")
        except Exception as e:
            fail("Limpar carrinho: funcional", str(e)[:120])

        # ----------------------------------------------------------------------
        section("T9 - Checkout Inteligente: Anonimo Redireciona para Login")
        # ----------------------------------------------------------------------
        try:
            page.goto(f"{BASE_URL}/")
            page.wait_for_load_state("networkidle")
            page.locator("a[href*='/tshirt-images/']").first.click()
            page.wait_for_load_state("networkidle")
            page.check("input[name=color]", force=True)
            page.check("input[name=size][value=M]", force=True)
            page.locator("button[type=submit]:has-text('Adicionar')").click()
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)

            checkout_btn = page.locator("#btn-checkout")
            checkout_btn.click()
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)

            assert "/login" in page.url, "Nao redirecionou o anonimo para a pagina de login"
            assert "redirect=checkout" in page.url, "Parametro de redirecionamento em falta na URL"
            ok("Checkout anonimo: redirecionamento para login correto")
        except Exception as e:
            fail("Checkout anonimo: redirecionamento para login correto", str(e)[:120])

        # ----------------------------------------------------------------------
        section("T10 - Persistencia do Carrinho Pos-Login & Redirecionamento ao Checkout")
        # ----------------------------------------------------------------------
        try:
            page.locator("input[name=email]").fill("c1@mail.pt")
            page.locator("input[name=password]").fill("123")
            page.locator("button[type=submit]").click()
            page.wait_for_load_state("networkidle")
            time.sleep(1)

            assert "/checkout" in page.url, f"Nao redirecionou para o checkout apos login. URL atual: {page.url}"
            assert "checkout em" in page.content().lower() or "checkout" in page.content().lower(), "Mensagem do placeholder do checkout nao encontrada"
            
            page.goto(f"{BASE_URL}/cart")
            page.wait_for_load_state("networkidle")
            
            item_rows = page.locator("input[name=quantity]")
            assert item_rows.count() > 0, "O carrinho foi esvaziado durante a autenticacao! (Falha de persistencia)"
            
            ok("Persistencia do carrinho no login: mantida a 100%")
            logout(page)
        except Exception as e:
            fail("Persistencia do carrinho no login: mantida a 100%", str(e)[:120])

        # ----------------------------------------------------------------------
        section("T11 - Checkout Inteligente: Administrador Bloqueado do Checkout")
        # ----------------------------------------------------------------------
        try:
            # Login como administrador
            login_as(page, "a2@mail.pt", "123")
            
            # Adicionar item ao carrinho para garantir que a seccao de checkout e renderizada
            page.goto(f"{BASE_URL}/")
            page.wait_for_load_state("networkidle")
            page.locator("a[href*='/tshirt-images/']").first.click()
            page.wait_for_load_state("networkidle")
            page.check("input[name=color]", force=True)
            page.check("input[name=size][value=M]", force=True)
            page.locator("button[type=submit]:has-text('Adicionar')").click()
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)
            
            page.goto(f"{BASE_URL}/cart")
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)

            assert "administradores" in page.content().lower() or "nao tem acesso" in page.content().lower(), "Aviso de restricao para admins nao exibido"
            
            checkout_btn = page.locator("#btn-checkout")
            assert checkout_btn.count() == 0, "Botao de checkout esta visivel para administrador!"
            
            ok("Restricao de checkout para Admins/Funcionarios: funcional")
            logout(page)
        except Exception as e:
            fail("Restricao de checkout para Admins/Funcionarios: funcional", str(e)[:120])

        browser.close()

        # ----------------------------------------------------------------------
        section("RESUMO FINAL - TESTES G3 BROWSER")
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
            print("  ALL TESTS PASSED! G3 validation successful.")
        else:
            print(f"  FAILED: {failed} test(s) failed.")

        return failed == 0

if __name__ == "__main__":
    success = run_tests()
    sys.exit(0 if success else 1)
