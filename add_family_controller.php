<?php
$file = file_get_contents('app/Http/Controllers/PublicadorController.php');

$familyMethods = <<<'METHODS'

    /**
     * Get family members for a publicador
     */
    public function familiares(Publicador $publicador)
    {
        $relaciones = \App\Models\RelacionFamiliar::where('publicador_id', $publicador->id)
            ->with('familiar:id,nombre,apellidos')
            ->get();

        $familiares = $relaciones->map(function($rel) {
            return [
                'familiar' => $rel->familiar,
                'tipo_relacion' => $rel->tipo_relacion,
                'tipo_label' => \App\Models\RelacionFamiliar::TIPOS[$rel->tipo_relacion] ?? $rel->tipo_relacion,
            ];
        });

        return response()->json(['familiares' => $familiares]);
    }

    /**
     * Get available publishers for family relationships
     */
    public function disponiblesFamilia(Publicador $publicador)
    {
        // Get IDs of current family members
        $familiarIds = \App\Models\RelacionFamiliar::where('publicador_id', $publicador->id)
            ->pluck('familiar_id')
            ->toArray();

        // Get all publishers from same congregation except self and current family
        $disponibles = Publicador::where('congregacion_id', $publicador->congregacion_id)
            ->where('id', '!=', $publicador->id)
            ->whereNotIn('id', $familiarIds)
            ->orderBy('apellidos')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'apellidos']);

        return response()->json(['disponibles' => $disponibles]);
    }

    /**
     * Add a family relationship
     */
    public function addFamiliar(Request $request, Publicador $publicador)
    {
        $request->validate([
            'familiar_id' => 'required|exists:publicadores,id',
            'tipo_relacion' => 'required|in:conyuge,progenitor,hijo',
        ]);

        try {
            \App\Models\RelacionFamiliar::crearRelacion(
                $publicador->id,
                $request->familiar_id,
                $request->tipo_relacion
            );

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Remove a family relationship
     */
    public function removeFamiliar(Request $request, Publicador $publicador)
    {
        $request->validate([
            'familiar_id' => 'required|exists:publicadores,id',
        ]);

        \App\Models\RelacionFamiliar::eliminarRelacion($publicador->id, $request->familiar_id);

        return response()->json(['success' => true]);
    }
METHODS;

// Check if methods already exist
if (strpos($file, 'function familiares') !== false) {
    echo "Family methods already exist\n";
    exit;
}

// Find the last closing brace of the class and insert before it
$lastBrace = strrpos($file, '}');
if ($lastBrace !== false) {
    $file = substr($file, 0, $lastBrace) . $familyMethods . "\n" . substr($file, $lastBrace);
    file_put_contents('app/Http/Controllers/PublicadorController.php', $file);
    echo "Family controller methods added successfully\n";
} else {
    echo "Could not find class closing brace\n";
}
