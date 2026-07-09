import React, { useState } from 'react';
import { BaseModal } from './BaseModal';
import { TextInput } from '../forms/TextInput';
import { AppButton } from '../buttons/AppButton';
import { useCustomerStore } from '../../stores/customerStore';
import { useToastStore } from '../../stores/toastStore';
import { useModalStore } from '../../stores/modalStore';
import { LocationPicker } from '../forms/LocationPicker';

export const CustomerModal: React.FC = () => {
  const { activeModal, closeModal } = useModalStore();
  const { addCustomer } = useCustomerStore();
  const { addToast } = useToastStore();

  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [phone, setPhone] = useState('');
  const [address, setAddress] = useState('');
  const [city, setCity] = useState('');
  const [latitude, setLatitude] = useState<number>();
  const [longitude, setLongitude] = useState<number>();
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!name || !phone) {
      addToast('Please enter customer name and phone number', 'warning');
      return;
    }
    setLoading(true);
    try {
      await addCustomer({ name, email, phone, address, city, latitude, longitude });
      addToast(`Customer "${name}" added!`, 'success');
      closeModal();
      setName('');
      setEmail('');
      setPhone('');
      setAddress('');
      setCity('');
      setLatitude(undefined);
      setLongitude(undefined);
    } catch (err) {
      addToast('Failed to add customer', 'danger');
    } finally {
      setLoading(false);
    }
  };

  return (
    <BaseModal
      isOpen={activeModal === 'CUSTOMER_CREATE'}
      onClose={closeModal}
      title="Add New Customer"
      footer={
        <>
          <AppButton variant="secondary" onClick={closeModal}>Cancel</AppButton>
          <AppButton variant="primary" onClick={handleSubmit} loading={loading}>Save Customer</AppButton>
        </>
      }
    >
      <form onSubmit={handleSubmit}>
        <TextInput label="Full Name *" value={name} onChange={(e) => setName(e.target.value)} placeholder="e.g. Jane Doe" />
        <TextInput label="Phone Number *" type="tel" value={phone} onChange={(e) => setPhone(e.target.value)} placeholder="+1 (555) 000-0000" />
        <TextInput label="Email Address" type="email" value={email} onChange={(e) => setEmail(e.target.value)} placeholder="jane@example.com" />
        <TextInput label="Address" value={address} onChange={(e) => setAddress(e.target.value)} placeholder="Street and building" />
        <TextInput label="City / Area" value={city} onChange={(e) => setCity(e.target.value)} placeholder="Doha" />
        <div className="form-group"><label className="form-label">Location Pin</label><LocationPicker latitude={latitude} longitude={longitude} onChange={(lat, lng) => { setLatitude(lat); setLongitude(lng); }} /></div>
      </form>
    </BaseModal>
  );
};
