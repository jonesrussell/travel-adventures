"""Verify the local Boost stdio MCP handshake and application-info tool."""
import argparse
import json
import queue
import subprocess
import sys
from pathlib import Path
import threading
import time

parser = argparse.ArgumentParser(description=__doc__)
parser.add_argument("--php", default="php", help="PHP executable for the application runtime")
arguments = parser.parse_args()
process = subprocess.Popen(
    [arguments.php, "artisan", "boost:mcp"],
    stdin=subprocess.PIPE,
    stdout=subprocess.PIPE,
    stderr=sys.stderr,
    cwd=Path(__file__).resolve().parents[1],
    text=True,
    bufsize=1,
)
responses = queue.Queue()


def read_responses():
    for line in process.stdout:
        responses.put(line)
    responses.put(None)


threading.Thread(target=read_responses, daemon=True).start()


def send(message):
    process.stdin.write(json.dumps(message) + "\n")
    process.stdin.flush()


def receive(request_id):
    deadline = time.monotonic() + 30
    while time.monotonic() < deadline:
        try:
            line = responses.get(timeout=1)
        except queue.Empty:
            continue
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
    url_tool = next(name for name in names if name.replace("-", "_").lower() == "get_absolute_url")
    send({"jsonrpc": "2.0", "id": 4, "method": "tools/call", "params": {"name": url_tool, "arguments": {"path": "/"}}})
    url_result = receive(4)
    if url_result.get("isError") or not url_result.get("content"):
        raise RuntimeError("GetAbsoluteUrl returned an error")
    print(json.dumps({"url": url_result["content"]}))
finally:
    process.terminate()
    try:
        process.wait(timeout=5)
    except subprocess.TimeoutExpired:
        process.kill()
        process.wait()
