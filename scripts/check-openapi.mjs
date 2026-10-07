import { existsSync } from 'node:fs';
import { spawnSync } from 'node:child_process';

const contract = 'contracts/openapi.yaml';
if (!existsSync(contract)) {
    // Foundation has no product API yet. Adding an API route requires a contract.
    const { readdirSync } = await import('node:fs');
    if (readdirSync('routes').includes('api.php')) {
        console.error('API routes exist without contracts/openapi.yaml.');
        process.exit(1);
    }
    console.log(
        'No product API contract yet; foundation-only validation skipped.',
    );
} else {
    const result = spawnSync(
        'node',
        ['node_modules/@redocly/cli/bin/cli.js', 'lint', contract],
        { stdio: 'inherit' },
    );
    process.exit(result.status ?? 1);
}
