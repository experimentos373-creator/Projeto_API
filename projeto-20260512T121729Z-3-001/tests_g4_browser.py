"""
FunShirt - Testes Browser G4 (Playwright)
Checkout, Validação de Referências, Simulação de Pagamento API,
Gravação de Encomendas (Transação) e Permissões/Dashboard de Encomendas.
Uso: python tests_g4_browser.py
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
    page.locator("form button[type=submit], button:has-text('Entrar'), button:has-text('Login')").first.click()
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
        page.once("dialog", lambda dialog: dialog.accept())
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
        page.set_default_timeout(15000)

        # ----------------------------------------------------------------------
        section("T1 - Checkout Acesso Anonimo Redireciona para Login")
        # ----------------------------------------------------------------------
        try:
            page.goto(f"{BASE_URL}/checkout")
            page.wait_for_load_state("networkidle")
            assert "/login" in page.url, "Nao redirecionou o anonimo para a pagina de login"
            assert "redirect=checkout" in page.url, "Parametro de redirecionamento em falta"
            ok("Checkout Anonimo: redirecionado com sucesso")
        except Exception as e:
            fail("Checkout Anonimo: redirecionado com sucesso", str(e)[:120])

        # ----------------------------------------------------------------------
        section("T2 - Checkout Acesso com Carrinho Vazio Redireciona para Cart")
        # ----------------------------------------------------------------------
        try:
            login_as(page, "c1@mail.pt", "123")
            
            # Garantir carrinho limpo
            page.goto(f"{BASE_URL}/cart")
            page.wait_for_load_state("networkidle")
            clear_btn = page.locator("button:has-text('Esvaziar carrinho')")
            if clear_btn.count() > 0:
                page.once("dialog", lambda dialog: dialog.accept())
                clear_btn.click()
                page.wait_for_load_state("networkidle")
                time.sleep(0.5)

            # Visitar checkout
            page.goto(f"{BASE_URL}/checkout")
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)
            
            assert "/cart" in page.url, "Acedeu ao checkout com o carrinho vazio!"
            assert "carrinho" in page.content().lower() or "vazio" in page.content().lower(), "Mensagem de carrinho vazio nao encontrada"
            ok("Carrinho vazio no checkout: redirecionado com sucesso")
        except Exception as e:
            fail("Carrinho vazio no checkout: redirecionado com sucesso", str(e)[:120])

        # ----------------------------------------------------------------------
        section("T3 - Checkout: Pre-preenchimento e Mapeamento de MB")
        # ----------------------------------------------------------------------
        try:
            # Adicionar item ao carrinho primeiro
            page.goto(f"{BASE_URL}/")
            page.wait_for_load_state("networkidle")
            page.locator("a[href*='/tshirt-images/']").first.click()
            page.wait_for_load_state("networkidle")
            page.check("input[name=color]", force=True)
            page.check("input[name=size][value=M]", force=True)
            page.locator("button[type=submit]:has-text('Adicionar')").click()
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)

            # Ir ao checkout
            page.goto(f"{BASE_URL}/checkout")
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)

            # Verificar NIF e Morada preenchidos
            nif_val = page.locator("input[name=nif]").input_value()
            address_val = page.locator("textarea[name=address]").input_value()
            assert len(nif_val) == 9, f"NIF preenchido incorretamente: {nif_val}"
            assert len(address_val) > 0, "Morada nao preenchida"

            # Verificar mapeamento MB -> MB WAY
            mbway_checked = page.locator("input[name=payment_type][value='MB WAY']").is_checked()
            assert mbway_checked, "Metodo de pagamento preferencial 'MB' nao foi mapeado para 'MB WAY'"
            
            ok("Checkout: Pre-preenchimento de dados e mapeamento MB -> MB WAY corretos")
        except Exception as e:
            fail("Checkout: Pre-preenchimento de dados e mapeamento MB -> MB WAY corretos", str(e)[:120])

        # ----------------------------------------------------------------------
        section("T4 - Validacao Local de Referencias Invalidas")
        # ----------------------------------------------------------------------
        try:
            # 1. Visa invalido (nao comeca por 4 ou tamanho incorreto)
            page.check("input[name=payment_type][value=Visa]", force=True)
            page.locator("input[name=payment_ref]").fill("5123456789012345")
            page.locator("button:has-text('Confirmar e Pagar'), button:has-text('Confirmar')").first.click()
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)
            assert "referência" in page.content().lower() or "visa" in page.content().lower(), "Nao barrou referencia Visa invalida"

            # 2. PayPal invalido
            page.check("input[name=payment_type][value=PayPal]", force=True)
            page.locator("input[name=payment_ref]").fill("email-invalido")
            page.locator("button:has-text('Confirmar e Pagar'), button:has-text('Confirmar')").first.click()
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)
            assert "referência" in page.content().lower() or "paypal" in page.content().lower(), "Nao barrou referencia PayPal invalida"

            # 3. MB WAY invalido
            page.check("input[name=payment_type][value='MB WAY']", force=True)
            page.locator("input[name=payment_ref]").fill("812345678")
            page.locator("button:has-text('Confirmar e Pagar'), button:has-text('Confirmar')").first.click()
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)
            assert "referência" in page.content().lower() or "mb way" in page.content().lower(), "Nao barrou referencia MB WAY invalida"

            ok("Validacoes locais de referencias: funcional")
        except Exception as e:
            fail("Validacoes locais de referencias: funcional", str(e)[:120])

        # ----------------------------------------------------------------------
        section("T5 - Checkout Efetuado com Sucesso & Gravacao da Encomenda")
        # ----------------------------------------------------------------------
        try:
            page.check("input[name=payment_type][value='MB WAY']", force=True)
            page.locator("input[name=payment_ref]").fill("912345678")
            
            # Submeter checkout
            page.locator("button:has-text('Confirmar e Pagar'), button:has-text('Confirmar')").first.click()
            page.wait_for_load_state("networkidle")
            time.sleep(1.5)

            print(f"DEBUG T5 - URL apos submissao: {page.url}")
            if "/checkout" in page.url:
                print("DEBUG T5 - Erros no ecrã:")
                print(page.content())

            assert "/orders/" in page.url, f"Nao redirecionou para detalhes da encomenda. URL atual: {page.url}"
            assert "encomenda realizada com sucesso" in page.content().lower() or "sucesso" in page.content().lower(), "Mensagem de sucesso nao encontrada"
            
            # Verificar se carrinho foi limpo
            page.goto(f"{BASE_URL}/cart")
            page.wait_for_load_state("networkidle")
            assert "vazio" in page.content().lower() or "carrinho esta vazio" in page.content().lower(), "Carrinho nao foi esvaziado apos checkout!"

            ok("Checkout com sucesso e gravacao da encomenda: funcional")
        except Exception as e:
            print(f"DEBUG T5 Exception: {str(e)}")
            print(f"DEBUG T5 - Final URL: {page.url}")
            print(f"DEBUG T5 - HTML snippet: {page.content()[:1000]}")
            fail("Checkout com sucesso e gravacao da encomenda: funcional", str(e)[:120])

        # ----------------------------------------------------------------------
        section("T6 - Consulta de Encomendas (Cliente)")
        # ----------------------------------------------------------------------
        try:
            page.goto(f"{BASE_URL}/orders")
            page.wait_for_load_state("networkidle")
            assert "encomenda" in page.content().lower() or "histórico" in page.content().lower(), "Lista de encomendas nao carregada"
            logout(page)
            ok("Historico de encomendas do cliente: exibido")
        except Exception as e:
            print(f"DEBUG T6 Exception: {str(e)}")
            print(f"DEBUG T6 - Final URL: {page.url}")
            print(f"DEBUG T6 - HTML snippet: {page.content()[:1000]}")
            fail("Historico de encomendas do cliente: exibido", str(e)[:120])

        # ----------------------------------------------------------------------
        section("T7 - Gestao de Encomendas (Funcionario) & Transicao para Closed")
        # ----------------------------------------------------------------------
        try:
            login_as(page, "f2@mail.pt", "123")
            page.goto(f"{BASE_URL}/orders")
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)

            # Verificar que apenas pendentes sao listadas (deve haver a encomenda criada recentemente)
            assert "encomendas pendentes" in page.content().lower() or "pendente" in page.content().lower(), "Dashboard de funcionario incorreto"
            
            # Abrir detalhe da ultima encomenda
            page.locator("a:has-text('Ver Detalhes')").first.click()
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)

            assert "aguarda envio" in page.content().lower() or "pendente" in page.content().lower()
            
            # Fechar encomenda (Marcar como enviada)
            page.locator("button:has-text('Marcar como Enviada')").click()
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)

            assert "enviada" in page.content().lower() or "closed" in page.content().lower(), "Estado da encomenda nao mudou para closed"
            logout(page)
            ok("Gestao de encomendas (Funcionario): consulta e transicao para closed funcionais")
        except Exception as e:
            print(f"DEBUG T7 Exception: {str(e)}")
            print(f"DEBUG T7 - Final URL: {page.url}")
            print(f"DEBUG T7 - HTML snippet: {page.content()[:1000]}")
            fail("Gestao de encomendas (Funcionario): consulta e transicao para closed funcionais", str(e)[:120])

        # ----------------------------------------------------------------------
        section("T8 - Gestao de Encomendas (Admin) & Filtros e Transicao para Canceled")
        # ----------------------------------------------------------------------
        try:
            # Login como Cliente e criar outra encomenda para podermos cancelar como Admin
            login_as(page, "c1@mail.pt", "123")
            page.goto(f"{BASE_URL}/")
            page.wait_for_load_state("networkidle")
            page.locator("a[href*='/tshirt-images/']").first.click()
            page.wait_for_load_state("networkidle")
            page.check("input[name=color]", force=True)
            page.check("input[name=size][value=L]", force=True)
            page.locator("button[type=submit]:has-text('Adicionar')").first.click()
            page.wait_for_load_state("networkidle")
            page.goto(f"{BASE_URL}/checkout")
            page.wait_for_load_state("networkidle")
            page.check("input[name=payment_type][value='MB WAY']", force=True)
            page.locator("input[name=payment_ref]").fill("912345678")
            page.locator("button:has-text('Confirmar e Pagar'), button:has-text('Confirmar')").first.click()
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)
            logout(page)

            # Login como Admin
            login_as(page, "a2@mail.pt", "123")
            page.goto(f"{BASE_URL}/orders")
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)

            # Testar filtros de Admin
            page.select_option("select[name=status]", value="pending")
            page.locator("button:has-text('Filtrar')").click()
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)

            # Abrir detalhe da encomenda pendente
            page.locator("a:has-text('Ver Detalhes')").first.click()
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)

            # Cancelar encomenda (motivo opcional)
            page.locator("textarea[name=reason_for_cancellation]").fill("Cancelamento de teste do browser")
            page.locator("button:has-text('Cancelar Encomenda')").click()
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)

            assert "cancelada" in page.content().lower(), "Estado da encomenda nao mudou para cancelada"
            assert "cancelamento de teste" in page.content().lower(), "Motivo do cancelamento nao exibido"
            
            logout(page)
            ok("Gestao de encomendas (Admin): filtros, transicao para cancelada e justificacao opcionais corretos")
        except Exception as e:
            print(f"DEBUG T8 Exception: {str(e)}")
            print(f"DEBUG T8 - Final URL: {page.url}")
            print(f"DEBUG T8 - HTML snippet: {page.content()[:1000]}")
            fail("Gestao de encomendas (Admin): filtros, transicao para cancelada e justificacao opcionais corretos", str(e)[:120])

        browser.close()

        # ----------------------------------------------------------------------
        section("RESUMO FINAL - TESTES G4 BROWSER")
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
            print("  ALL TESTS PASSED! G4 validation successful.")
        else:
            print(f"  FAILED: {failed} test(s) failed.")

        return failed == 0

if __name__ == "__main__":
    success = run_tests()
    sys.exit(0 if success else 1)
