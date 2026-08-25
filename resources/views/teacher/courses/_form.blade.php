@csrf
@if(isset($course)) @method('PUT') @endif
<div class="teacher-form-group">
    <label for="name">Nombre del curso</label>
    <input id="name" name="name" class="teacher-input" required maxlength="100" value="{{ old('name', $course->name ?? '') }}" placeholder="Ej: 3.º Medio D">
    @error('name')<div class="teacher-error">{{ $message }}</div>@enderror
</div>
<div class="teacher-form-group">
    <label for="school_name">Colegio <span style="font-weight:400; color:var(--text-secondary);">(opcional por ahora)</span></label>
    <input id="school_name" name="school_name" class="teacher-input" maxlength="150" value="{{ old('school_name', $course->school_name ?? '') }}" placeholder="Nombre del establecimiento">
    @error('school_name')<div class="teacher-error">{{ $message }}</div>@enderror
</div>
<button class="teacher-btn teacher-btn-primary" type="submit">{{ isset($course) ? 'Guardar cambios' : 'Crear curso' }}</button>
