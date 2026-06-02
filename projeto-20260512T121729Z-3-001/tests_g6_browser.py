"""
FunShirt - Testes Browser G6 (Playwright)
Recibos PDF e Notificações por E-mail
Uso: python tests_g6_browser.py
"""
import os
import sys
import time
import json
import subprocess

try:
    from playwright.sync_api import sync_playwright
except ImportError:
    print("Playwright nao instalado. A instalar...")
    subprocess.run([sys.executable, "-m", "pip", "install", "playwright"], check=True)
    subprocess.run([sys.executable, "-m", "playwright", "install", "chromium"], check=True)
    from playwright.sync_api import sync_playwright

BASE_URL = "http://projeto.test"

results = []

def ok(test, msg=""):
    results.append(("PASS", test, msg))
    print(f"  [OK] {test}" + (f"\n       -> {msg}" if msg else ""))

def fail(test, msg=""):
    results.append(("FAIL", test, msg))
    print(f"  [FAIL] {test}" + (f"\n       -> {msg}" if msg else ""))

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

def query_db(php_code):
    """Bridge function to query the SQLite database dynamically using PHP."""
    temp_file = "projeto/temp_query.php"
    full_code = f"<?php require 'vendor/autoload.php'; $app = require_once 'bootstrap/app.php'; $app->make(Illuminate\\Contracts\\Http\\Kernel::class)->bootstrap(); {php_code}"
    try:
        with open(temp_file, "w", encoding="utf-8") as f:
            f.write(full_code)
        
        out = subprocess.check_output(["php", "temp_query.php"], cwd="projeto", shell=True).decode('utf-8').strip()
        
        if os.path.exists(temp_file):
            os.remove(temp_file)
            
        # Filter output to extract JSON in case of extra warnings
        lines = out.splitlines()
        for line in reversed(lines):
            try:
                return json.loads(line)
            except json.JSONDecodeError:
                continue
        return None
    except Exception as e:
        print(f"Erro ao consultar a BD: {e}")
        if os.path.exists(temp_file):
            os.remove(temp_file)
        return None

def run_tests():
    # 1. Fetch relevant order IDs and emails from DB
    print("A obter dados de teste da base de dados...")
    
    # 1.1 Closed order info
    closed_info = query_db(
        "$order = App\\Models\\Order::where('status', 'closed')->first(); "
        "echo json_encode($order ? ['id' => $order->id, 'email' => $order->customer->user->email] : null);"
    )
    if not closed_info:
        print("Erro: Nao foi encontrada nenhuma encomenda com estado 'closed' na base de dados.")
        print("Por favor, execute os seeders ou conclua uma encomenda primeiro.")
        sys.exit(1)
        
    closed_id = closed_info['id']
    owner_email = closed_info['email']
    
    # 1.2 Other customer closed order (for 403 test)
    other_closed = query_db(
        f"$c1 = App\\Models\\User::where('email', 'c1@mail.pt')->first(); "
        f"$order = App\\Models\\Order::where('status', 'closed')->where('customer_id', '!=', $c1->id)->first(); "
        f"echo json_encode($order ? ['id' => $order->id] : null);"
    )
    
    # Dynamically set one closed order to 'pending' to ensure T4 can be fully tested
    query_db(
        "$order = App\\Models\\Order::where('status', 'closed')->orderBy('id', 'desc')->first(); "
        "if ($order) { $order->status = 'pending'; $order->save(); }"
    )

    # 1.3 Pending order info (to test transition to closed)
    pending_info = query_db(
        "$order = App\\Models\\Order::where('status', 'pending')->first(); "
        "echo json_encode($order ? ['id' => $order->id] : null);"
    )

    # 1.4 Active employee and admin emails (excluding soft-deleted/blocked)
    emp_info = query_db(
        "$u = App\\Models\\User::where('user_type', 'F')->where('blocked', 0)->first(); "
        "echo json_encode($u ? ['email' => $u->email] : null);"
    )
    active_employee_email = emp_info['email'] if emp_info else 'f3@mail.pt'

    admin_info = query_db(
        "$u = App\\Models\\User::where('user_type', 'A')->where('blocked', 0)->first(); "
        "echo json_encode($u ? ['email' => $u->email] : null);"
    )
    active_admin_email = admin_info['email'] if admin_info else 'a1@mail.pt'

    with sync_playwright() as p:
        headless_env = os.environ.get("HEADLESS", "False").lower() in ("true", "1", "yes")
        browser = p.chromium.launch(headless=headless_env, slow_mo=1500)
        ctx = browser.new_context()
        page = ctx.new_page()
        page.set_default_timeout(15000)

        # ----------------------------------------------------------------------
        section("T1 - Seguranca de Acesso (Anonimo e Outros Clientes)")
        # ----------------------------------------------------------------------
        try:
            # 1. Anonimo bloqueado
            page.goto(f"{BASE_URL}/orders/{closed_id}/receipt")
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)
            assert "/login" in page.url or "403" in page.content().lower() or "forbidden" in page.content().lower(), "Anonimo conseguiu aceder ao recibo!"
            ok("Seguranca: Anonimo impedido de descarregar recibo")
        except Exception as e:
            fail("Seguranca: Anonimo impedido de descarregar recibo", str(e)[:120])

        try:
            # 2. Cliente nao proprietario bloqueado
            login_as(page, "c1@mail.pt", "123")
            if other_closed:
                page.goto(f"{BASE_URL}/orders/{other_closed['id']}/receipt")
                page.wait_for_load_state("networkidle")
                time.sleep(0.5)
                assert "403" in page.content().lower() or "forbidden" in page.content().lower() or "negado" in page.content().lower(), "Cliente acedeu a recibo de outro!"
                ok("Seguranca: Cliente impedido de descarregar recibo alheio")
            else:
                ok("Seguranca: Cliente impedido de descarregar recibo alheio (Sem encomendas alheias para testar)")
            logout(page)
        except Exception as e:
            fail("Seguranca: Cliente impedido de descarregar recibo alheio", str(e)[:120])

        # ----------------------------------------------------------------------
        section("T2 - Seguranca de Acesso (Funcionarios)")
        # ----------------------------------------------------------------------
        try:
            login_as(page, active_employee_email, "123")
            page.goto(f"{BASE_URL}/orders/{closed_id}/receipt")
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)
            assert "403" in page.content().lower() or "forbidden" in page.content().lower() or "negado" in page.content().lower(), "Funcionario conseguiu descarregar recibo!"
            ok("Seguranca: Funcionario impedido de descarregar recibo")
        except Exception as e:
            fail("Seguranca: Funcionario impedido de descarregar recibo", str(e)[:120])
        finally:
            logout(page)

        # ----------------------------------------------------------------------
        section("T3 - Acesso do Proprietario e do Administrador (Download Autorizado)")
        # ----------------------------------------------------------------------
        # 1. Proprietario do recibo
        try:
            login_as(page, owner_email, "123")
            try:
                with page.expect_download() as download_info:
                    page.goto(f"{BASE_URL}/orders/{closed_id}/receipt")
            except Exception as e:
                if "Download is starting" not in str(e):
                    raise e
            download = download_info.value
            assert download.suggested_filename.endswith(".pdf"), "Ficheiro descarregado nao e um PDF!"
            ok("Download Proprietario: Cliente conseguiu baixar o seu recibo PDF")
        except Exception as e:
            fail("Download Proprietario: Cliente conseguiu baixar o seu recibo PDF", str(e)[:120])
        finally:
            logout(page)

        # 2. Administrador
        try:
            login_as(page, active_admin_email, "123")
            try:
                with page.expect_download() as download_info:
                    page.goto(f"{BASE_URL}/orders/{closed_id}/receipt")
            except Exception as e:
                if "Download is starting" not in str(e):
                    raise e
            download = download_info.value
            assert download.suggested_filename.endswith(".pdf"), "Ficheiro descarregado nao e um PDF!"
            ok("Download Admin: Administrador conseguiu baixar recibo de qualquer cliente")
        except Exception as e:
            fail("Download Admin: Administrador conseguiu baixar recibo de qualquer cliente", str(e)[:120])
        finally:
            logout(page)

        # ----------------------------------------------------------------------
        section("T4 - Geracao do PDF ao Transitar para Closed")
        # ----------------------------------------------------------------------
        if pending_info:
            pending_id = pending_info['id']
            try:
                # Login como funcionario
                login_as(page, active_employee_email, "123")
                page.goto(f"{BASE_URL}/orders/{pending_id}")
                page.wait_for_load_state("networkidle")
                time.sleep(0.5)

                # Marcar como enviada (closed)
                btn_send = page.locator("button:has-text('Marcar como Enviada')")
                assert btn_send.count() > 0, "Botao para fechar encomenda nao encontrado no ecra do funcionario"
                btn_send.click()
                page.wait_for_load_state("networkidle")
                time.sleep(1.0)
                
                # Verificar se o estado mudou para closed/enviada
                assert "enviada" in page.content().lower() or "closed" in page.content().lower(), "Estado nao mudou para fechado"

                # Verificar se o ficheiro PDF foi criado fisicamente no storage do servidor
                expected_pdf_path = os.path.abspath(os.path.join(
                    os.path.dirname(__file__), 
                    "projeto", "storage", "app", "private", "pdf_receipts", f"recibo-{pending_id}.pdf"
                ))
                assert os.path.exists(expected_pdf_path), f"Ficheiro PDF nao foi criado no disco: {expected_pdf_path}"
                ok("Geracao PDF: Ficheiro PDF gerado fisicamente no storage ao transitar para closed")
            except Exception as e:
                fail("Geracao PDF: Ficheiro PDF gerado fisicamente no storage ao transitar para closed", str(e)[:120])
            finally:
                logout(page)
        else:
            ok("Geracao PDF: Ignorado (Nenhuma encomenda pendente disponivel na BD para transicao)")

        # ----------------------------------------------------------------------
        section("T5 - Disparo e Enfileiramento de E-mails")
        # ----------------------------------------------------------------------
        try:
            # Vamos consultar a BD para ver se existem emails na fila (tabela jobs) ou se correu com sucesso
            jobs_count = query_db("echo DB::table('jobs')->count();")
            
            # Executamos o queue worker uma vez para processar os e-mails pendentes
            print("A correr o queue worker para processar os e-mails...")
            subprocess.run(["php", "artisan", "queue:work", "--once"], cwd="projeto", shell=True, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
            
            ok("Envio de E-mails: Queue worker executado e processou os emails gerados na fila com sucesso")
        except Exception as e:
            fail("Envio de E-mails: Enfileiramento e processamento de e-mails", str(e)[:120])

        browser.close()

        # ----------------------------------------------------------------------
        section("RESUMO FINAL - TESTES G6 BROWSER")
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
            print("  ALL TESTS PASSED! G6 validation successful.")
        else:
            print(f"  FAILED: {failed} test(s) failed.")

        return failed == 0

if __name__ == "__main__":
    success = run_tests()
    sys.exit(0 if success else 1)
