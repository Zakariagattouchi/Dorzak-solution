import React, { useState } from 'react';
import { CircleMarker, MapContainer, TileLayer, useMap, useMapEvents } from 'react-leaflet';
import { AppButton } from '../buttons/AppButton';
import { TextInput } from './TextInput';
import 'leaflet/dist/leaflet.css';

interface LocationPickerProps {
  latitude?: number;
  longitude?: number;
  onChange: (latitude: number, longitude: number) => void;
  onAddressResolved?: (address: string, city?: string) => void;
}

const MapEvents: React.FC<LocationPickerProps> = ({ latitude, longitude, onChange }) => {
  const map = useMap();
  useMapEvents({ click: (event) => onChange(event.latlng.lat, event.latlng.lng) });
  React.useEffect(() => {
    const id = setTimeout(() => map.invalidateSize(), 50);
    return () => clearTimeout(id);
  }, [map]);
  React.useEffect(() => {
    if (latitude != null && longitude != null)
      map.flyTo([latitude, longitude], Math.max(map.getZoom(), 14));
  }, [latitude, longitude, map]);
  return latitude != null && longitude != null ? (
    <CircleMarker
      center={[latitude, longitude]}
      radius={9}
      pathOptions={{ color: '#0f766e', fillColor: '#14b8a6', fillOpacity: 1 }}
    />
  ) : null;
};

export const LocationPicker: React.FC<LocationPickerProps> = ({
  latitude,
  longitude,
  onChange,
  onAddressResolved,
}) => {
  const [query, setQuery] = useState('');
  const [searching, setSearching] = useState(false);
  const center: [number, number] =
    latitude != null && longitude != null ? [latitude, longitude] : [25.2854, 51.531];

  const resolveAddress = async (lat: number, lon: number) => {
    if (!onAddressResolved) return;
    try {
      const response = await fetch(
        `https://nominatim.openstreetmap.org/reverse?format=json&addressdetails=1&lat=${lat}&lon=${lon}`,
      );
      const result = await response.json();
      const city =
        result.address?.city ||
        result.address?.town ||
        result.address?.village ||
        result.address?.municipality;
      if (result.display_name) onAddressResolved(result.display_name, city);
    } catch {
      /* Coordinates remain usable if address lookup is unavailable. */
    }
  };

  const selectPosition = (lat: number, lon: number, address?: string, city?: string) => {
    onChange(lat, lon);
    if (address && onAddressResolved) onAddressResolved(address, city);
    else resolveAddress(lat, lon);
  };

  const search = async () => {
    if (!query.trim()) return;
    setSearching(true);
    try {
      const response = await fetch(
        `https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&limit=1&q=${encodeURIComponent(query)}`,
      );
      const [result] = await response.json();
      if (result) {
        const city =
          result.address?.city ||
          result.address?.town ||
          result.address?.village ||
          result.address?.municipality;
        selectPosition(Number(result.lat), Number(result.lon), result.display_name, city);
      }
    } finally {
      setSearching(false);
    }
  };

  const locate = () =>
    navigator.geolocation.getCurrentPosition((position) =>
      selectPosition(position.coords.latitude, position.coords.longitude),
    );

  return (
    <div className="location-picker">
      <div className="location-tools">
        <TextInput
          aria-label="Search location"
          placeholder="Search address or area"
          value={query}
          onChange={(event) => setQuery(event.target.value)}
        />
        <AppButton type="button" variant="secondary" loading={searching} onClick={search}>
          Search
        </AppButton>
        <AppButton type="button" variant="secondary" icon="mapPin" onClick={locate}>
          Current Location
        </AppButton>
      </div>
      <MapContainer center={center} zoom={12} scrollWheelZoom className="location-map">
        <TileLayer
          attribution="&copy; OpenStreetMap contributors"
          url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
        />
        <MapEvents latitude={latitude} longitude={longitude} onChange={selectPosition} />
      </MapContainer>
      <small>Click the map to place the delivery pin.</small>
    </div>
  );
};
