<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Palabras que antes vivían hardcodeadas en SpamFilterService.
     * Se siembran aquí para que el despliegue de esta migración no
     * desactive silenciosamente la protección antispam ya existente.
     *
     * @var array<string>
     */
    private const SEED_WORDS = [
        'compra ahora',
        'oferta increíble',
        'gana dinero',
        'trabaja desde casa',
        'haz clic aquí',
        'gratis',
        'bitcoin gratis',
        'criptomonedas fácil',
        'préstamo rápido',
        'inversión garantizada',
        'viagra',
        'casino online',
        'apuestas deportivas',
        'contenido adulto',
        'verifica tu cuenta',
        'actualiza tus datos',
        'cuenta suspendida',
        'ganaste un premio',
    ];

    public function up(): void
    {
        Schema::create('blacklist_words', function (Blueprint $table) {
            $table->id();
            $table->string('word', 191)->unique();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('is_active');
        });

        $now = now();
        DB::table('blacklist_words')->insert(array_map(
            fn (string $word) => [
                'word' => $word,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            self::SEED_WORDS
        ));
    }

    public function down(): void
    {
        Schema::dropIfExists('blacklist_words');
    }
};
