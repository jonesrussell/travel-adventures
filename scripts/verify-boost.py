"""Verify the local Boost stdio MCP handshake and application-info tool."""
import json
import selectors
import subprocess
import time

process = subprocess.Popen(
    ["./vendor/bin/sail", "artisan", "boost:mcp"],
    stdin=subprocess.PIPE,
    stdout=subprocess.PIPE,
    stderr=subprocess.PIPE,
    text=True,
    bufsize=1,
)
selector = selectors.DefaultSelector()
selector.register(process.stdout, selectors.EVENT_READ)


def send(message):
    process.stdin.write(json.dumps(message) + "\n")
    process.stdin.flush()


def receive(request_id):
    deadline = time.monotonic() + 30
    while time.monotonic() < deadline:
        if not selector.select(timeout=1):
            continue
        line = process.stdout.readline()
        if not line:
            raise RuntimeError("Boost exited before responding")
        response = json.loads(line)
        if response.get("id") == request_id:
            if "error" in response:
                raise RuntimeError(str(response["error"]))
            return response["result"]
    raise TimeoutError("Boost did not respond within 30 seconds")


try:
    send({"jsonrpc": "2.0", "id": 1, "method": "initialize", "params": {
        "protocolVersion": "2024-11-05", "capabilities": {},
        "clientInfo": {"name": "travel-foundation-verifier", "version": "1.0"},
    }})
    receive(1)
    send({"jsonrpc": "2.0", "method": "notifications/initialized"})
    send({"jsonrpc": "2.0", "id": 2, "method": "tools/list"})
    names = [tool["name"] for tool in receive(2)["tools"]]
    name = next(name for name in names if name in ("application-info", "application_info"))
    send({"jsonrpc": "2.0", "id": 3, "method": "tools/call", "params": {"name": name, "arguments": {}}})
    result = receive(3)
    if result.get("isError") or not result.get("content"):
        raise RuntimeError("Application-info returned an error or empty content")
    print(json.dumps({"mcp": "verified", "tool": name, "tool_count": len(names)}))
finally:
    selector.close()
    process.terminate()
    try:
        process.wait(timeout=5)
    except subprocess.TimeoutExpired:
        process.kill()
        process.wait()
